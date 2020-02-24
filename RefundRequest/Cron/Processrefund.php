<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 5/26/19
 * Time: 1:20 PM
 */

namespace Doku\RefundRequest\Cron;


use Doku\RefundRequest\Model\Attribute\Source\RefundType;
use Magento\Sales\Model\Order;
use \Doku\RefundRequest\Model\ResourceModel\Request\CollectionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use \Doku\Core\Model\GeneralConfiguration;
use \Doku\Core\Helper\Data;
use \Doku\RefundRequest\Helper\Data as RefundHelper;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Service\InvoiceService;
use \Doku\Core\Api\TransactionRepositoryInterface;

class Processrefund
{
    protected $session;
    protected $order;
    protected $logger;
    protected $resourceConnection;
    protected $config;
    protected $helper;
    protected $builderInterface;
    protected $_timezone;
    protected $invoiceService;
    protected $collectionFactory;
    protected $_generalConfiguration;

    public function __construct(
        Session $session,
        Order $order,
        ResourceConnection $resourceConnection,
        GeneralConfiguration $config,
        Data $helper,
        RefundHelper $refundHelper,
        LoggerInterface $logger,
        TransactionRepositoryInterface $transactionRepository,
        BuilderInterface $builderInterface,
        TimezoneInterface $timezone,
        InvoiceService $_invoiceService,
        CollectionFactory $collectionFactory,
        GeneralConfiguration $generalConfiguration
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->order = $order;
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        $this->helper = $helper;
        $this->refundHelper = $refundHelper;
        $this->builderInterface = $builderInterface;
        $this->invoiceService = $_invoiceService;
        $this->transactionRepository = $transactionRepository;
        $this->_timezone = $timezone;
        $this->collectionFactory = $collectionFactory;
        $this->_generalConfiguration = $generalConfiguration;
    }



    public function execute() {
        $this->helper->logger('cron refund process started','DOKU_refund');

        $now = new \DateTime();
        $now->add(new \DateInterval("PT7H"));
        $scheduledCollection = $this->collectionFactory->create();
        $scheduledCollection->addFieldToFilter('refund_status', \Doku\RefundRequest\Model\Attribute\Source\Status::ACCEPT);
        $scheduledCollection->addFieldToFilter('doku_status', ['null' => true]);
        $scheduledCollection->setOrder('created_at', 'ASC');

        $mallId = $this->_generalConfiguration->getMallId();
        $chainId = $this->_generalConfiguration->getChainId();
        $sharedKey = $this->_generalConfiguration->getSharedKey();


        $this->helper->logger($scheduledCollection->getSelectSql(),'DOKU_refund');


        foreach($scheduledCollection as $refund) {

            $_dokuTrans = $this->transactionRepository->getByTransIdMerchant($refund->getIncrementId());
            $refundResult = false;

            if($refund->getDokuRefundType() == \Doku\RefundRequest\Model\Attribute\Source\RefundType::REFUND_FULL) {
                $refundAmount = $_dokuTrans->getDokuGrandTotal()+0;
                $refundAmount = number_format($refundAmount,2,".","");
                $param = array(
                    'MALLID' => $mallId,
                    'CHAINMERCHANT' => $chainId,
                    'TRANSIDMERCHANT' => $refund->getIncrementId(),
                    'REFIDMERCHANT' => $refund->getIncrementId()."-".$refund->getId(),
                    'APPROVALCODE' => $_dokuTrans->getApprovalCode(),
                    'AMOUNT' => $refundAmount,
                    'CURRENCY' => "360",
                    "REFUNDTYPE" => "01",
                    'SESSIONID' => $refund->getIncrementId(),
                    'REASON' => $refund->getReasonOption()
                );

            } else if($refund->getDokuRefundType() == \Doku\RefundRequest\Model\Attribute\Source\RefundType::REFUND_PARTIAL){
                $refundAmount = $refund->getRefundAmount()+0;
                $refundAmount = number_format($refundAmount,2,".","");
                $param = array(
                    'MALLID' => $mallId,
                    'CHAINMERCHANT' => $chainId,
                    'TRANSIDMERCHANT' => $refund->getIncrementId(),
                    'REFIDMERCHANT' => $refund->getIncrementId()."-".$refund->getId(),
                    'APPROVALCODE' => $_dokuTrans->getApprovalCode(),
                    'AMOUNT' => $refundAmount,
                    'CURRENCY' => "360",
                    "REFUNDTYPE" => "02",
                    'SESSIONID' => $refund->getIncrementId(),
                    'REASON' => $refund->getReasonOption()
                );
            } else if($refund->getDokuRefundType() == \Doku\RefundRequest\Model\Attribute\Source\RefundType::RETURN_STOCK){
                $refundAmount = $refund->getRefundAmount()+0;
                $refundAmount = number_format($refundAmount,2,".","");
                if($refund->getRefundAmount() > 0) {
                    $param = array(
                        'MALLID' => $mallId,
                        'CHAINMERCHANT' => $chainId,
                        'TRANSIDMERCHANT' => $refund->getIncrementId(),
                        'REFIDMERCHANT' => $refund->getIncrementId()."-".$refund->getId(),
                        'APPROVALCODE' => $_dokuTrans->getApprovalCode(),
                        'AMOUNT' => $refundAmount,
                        'CURRENCY' => "360",
                        "REFUNDTYPE" => "02",
                        'REASON' => $refund->getReasonOption(),
                        'SESSIONID' => $refund->getIncrementId(),
                    );
                } else {
                    continue;
                }
            }

            $param['WORDS'] = sha1($param['AMOUNT'] . $mallId . $sharedKey . $param['REFIDMERCHANT'] . $param['SESSIONID']);

            try {
                $refundResult = $this->refundHelper->doRefund($param);
                if($refundResult['RESPONSECODE'] == '0000' && $refundResult['RESPONSEMSG'] == 'SUCCESS') {
                    $refund->setRefundStatus(\Doku\RefundRequest\Model\Attribute\Source\Status::REFUNDED);
                    $table = $this->resourceConnection->getTableName('sales_order_grid');
                    $this->updateData(
                        $table,
                        ['refund_status' => $$refund->getRefundStatus],
                        "increment_id = $refund->getIncrementId()"
                    );
                }
                $refund->setDokuStatus($refundResult['RESPONSEMSG']);
                $refund->save();
                $this->helper->logger('===== Refund for order: ' . $_dokuTrans->getTransIdMerchant() . ' ===== Finished','DOKU_refund');
            } catch (\Exception $e) {
                $this->helper->logger('===== Refund for order: ' . $_dokuTrans->getTransIdMerchant() . ' ===== Failed','DOKU_refund');
                continue;
            }

        }

    }




}
