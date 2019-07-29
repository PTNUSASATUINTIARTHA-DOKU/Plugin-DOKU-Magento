<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 5/26/19
 * Time: 1:20 PM
 */

namespace Doku\Core\Cron;


use Magento\Sales\Model\Order;
use \Doku\Core\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Doku\Core\Model\GeneralConfiguration;
use Doku\Core\Helper\Data;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Service\InvoiceService;

class Voidauth
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
        LoggerInterface $logger,
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
        $this->builderInterface = $builderInterface;
        $this->invoiceService = $_invoiceService;
        $this->_timezone = $timezone;
        $this->collectionFactory = $collectionFactory;
        $this->_generalConfiguration = $generalConfiguration;
    }



    public function execute() {

        $now = new \DateTime();
        $now->add(new \DateInterval("PT7H"));
        $scheduledCollection = $this->collectionFactory->create();
        $scheduledCollection->addFieldToFilter('authorization_status', 'authorization');
        $scheduledCollection->addFieldToFilter('auth_expired', ['lt' => $now]);
        $scheduledCollection->setOrder('auth_expired', 'ASC');

        $mallId = $this->_generalConfiguration->getMallId();
        $chainId = $this->_generalConfiguration->getChainId();
        $sharedKey = $this->_generalConfiguration->getSharedKey();

        foreach($scheduledCollection as $dokuorder) {
            $this->logger->info('===== Void payment for order: ' . $dokuorder->getTransIdMerchant() . ' ===== Start');

            $json = $dokuorder->getRequestParams();
            $requestData = json_decode($json, TRUE);

            $param = array(
                'MALLID' => $mallId,
                'CHAINMERCHANT' => $chainId,
                'TRANSIDMERCHANT' => $dokuorder->getTransIdMerchant(),
                'SESSIONID' => $requestData['SESSIONID'],
                'PAYMENTCHANNEL' => '15', //only for CC

            );

            $param['WORDS'] = sha1($mallId . $sharedKey . $dokuorder->getTransIdMerchant() . $param['SESSIONID']);

            try {
                $void = $this->_helper->doVoid($param);
                $this->logger->info('===== Void payment for order: ' . $dokuorder->getTransIdMerchant() . ' ===== Finished');
            } catch (\Exception $e) {
                $this->logger->info('===== Void payment for order: ' . $dokuorder->getTransIdMerchant() . ' ===== Failed');
                continue;
            }

        }

    }


}