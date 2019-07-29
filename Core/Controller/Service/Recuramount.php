<?php
namespace Doku\Core\Controller\Service;

use \Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Service\InvoiceService;
use \Psr\Log\LoggerInterface;
use Doku\Core\Model\GeneralConfiguration;
use Doku\Core\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Recuramount extends \Magento\Framework\App\Action\Action {

    protected $resourceConnection;
    protected $order;
    protected $generalConfiguration;
    protected $logger;
    protected $invoiceService;
    protected $builderInterface;
    protected $coreHelper;
    protected $timezoneInterface;

    public function __construct(
        LoggerInterface $loggerInterface,
        Context $context,
        ResourceConnection $resourceConnection,
        Order $order,
        BuilderInterface $_builderInterface,
        InvoiceService $_invoiceService,
        GeneralConfiguration $_generalConfiguration,
        Data $_coreHelper,
        TimezoneInterface $timezoneInterface
    ) {
        parent::__construct(
            $context
        );

        $this->resourceConnection = $resourceConnection;
        $this->order = $order;
        $this->builderInterface = $_builderInterface;
        $this->invoiceService = $_invoiceService;
        $this->logger = $loggerInterface;
        $this->generalConfiguration = $_generalConfiguration;
        $this->coreHelper = $_coreHelper;
        $this->timezoneInterface = $timezoneInterface;
    }

    public function execute()
    {
        $this->logger->info('===== Recur Amount Controller ===== Start');
        try {
            $this->logger->info('===== Recur Amount Controller ===== Checking whitlist IP');
            if (!empty($this->generalConfiguration->getIpWhitelist())) {
                $ipWhitelist = explode(",", $this->generalConfiguration->getIpWhitelist());

                $clientIp = $this->coreHelper->getClientIp();

                if (!in_array($clientIp, $ipWhitelist)) {
                    $this->logger->info('===== Recur Amount Controller ===== IP not found');
                    return;
                }
            }

            $postjson = json_encode($_POST, JSON_PRETTY_PRINT);
            $this->logger->info('post : '. $postjson);
            $postData = $_POST;
            $this->logger->info('===== Recur Amount Controller ===== Checking done');
            $this->logger->info('===== Recur Amount Controller ===== Finding order...');

            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('doku_transaction');

            $sql = "SELECT * FROM " . $tableName . " where recurring_billnumber = '" . $postData['BILLNUMBER'] . "' AND recurring_flatstatus = 0 AND customer_email = '".$postData['CUSTOMERID']."'";
            $dokuOrder = $connection->fetchRow($sql);

            if(!isset($dokuOrder['recurring_billnumber'])){
                $this->logger->info('===== Recur Amount Controller ===== Billnumber Merchant not found! in doku_transaction table');
                return;
            }

            $this->logger->info('===== Recur Amount Controller ===== Order found');
            $this->logger->info('===== Recur Amount Controller ===== Updating order...');

            $order = $this->order->loadByIncrementId($dokuOrder['trans_id_merchant']);

            if (!$order->getId()) {
                $this->logger->info('===== Recur Amount Controller ===== Order not found!');
                return;
            }

            $requestParams = json_decode($dokuOrder['request_params'], true);
            $mallId = isset($requestParams['MALLID'])?$requestParams['MALLID']:$requestParams['req_mall_id'];
            $sharedKey = $requestParams['SHAREDID'];

            // CUSTOMERID + <shared key> + BILLNUMBER.
            $words = sha1($postData['CUSTOMERID'] . $sharedKey . $postData['BILLNUMBER']);
            $this->logger->info('words raw : '. $postData['CUSTOMERID'] . $sharedKey . $postData['BILLNUMBER']);
            $this->logger->info('words : '. $words);
            $this->logger->info('===== Recur Amount Controller ===== Checking words...');

            if ($postData['WORDS'] != $words) {
                $this->logger->info('===== Recur Amount Controller ===== Words not match!');
                return;
            }

            //echo $this->generalConfiguration->getRecurringCharge();
            echo number_format($dokuOrder['doku_grand_total'], 2, ".", "");

            $this->logger->info('===== Recur Amount Controller ===== Updating success...');
            $this->logger->info('===== Recur Amount Controller ===== End');

        } catch(\Exception $e) {
            $this->logger->info('===== Recur Amount Controller ===== Generate code error : '. $e->getMessage());
            $this->logger->info('===== Recur Amount Controller ===== End');
            return;
        }
    }
}
