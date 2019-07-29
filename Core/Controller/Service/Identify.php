<?php
namespace Doku\Core\Controller\Service;

use \Magento\Framework\App\Action\Context;
use \Doku\Core\Model\GeneralConfiguration;
use Magento\Framework\App\ResourceConnection;
//use Magento\Sales\Model\Order;
//use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
//use Magento\Sales\Model\Service\InvoiceService;
use \Psr\Log\LoggerInterface;
use Doku\Core\Helper\Data;

class Identify extends \Magento\Framework\App\Action\Action {

    protected $resourceConnection;
//    protected $order;
//    protected $dokuHostedConfigProvider;
    protected $generalConfiguration;
    protected $logger;
    protected $coreHelper;


    public function __construct(
        LoggerInterface $loggerInterface,
            Data $_coreHelper,

        GeneralConfiguration $config,
        ResourceConnection $resourceConnection,
//        Order $order,
//        BuilderInterface $builderInterface,
//        InvoiceService $invoiceService
                    Context $context
    )
    {
        parent::__construct(
            $context
        );

        $this->resourceConnection = $resourceConnection;
//        $this->order = $order;
//        $this->builderInterface = $builderInterface;
//        $this->invoiceService = $invoiceService;
        $this->generalConfiguration = $config;
        $this->logger = $loggerInterface;
        $this->coreHelper = $_coreHelper;
    }

    public function execute()
    {
        $this->logger->info('===== Identify Controller ===== Start');
        
        $this->logger->info('===== Identify Controller ===== Checking whitlist IP');

        if (!empty($this->generalConfiguration->getIpWhitelist())) {
            $ipWhitelist = explode(",", $this->generalConfiguration->getIpWhitelist());

            $clientIp = $this->coreHelper->getClientIp();

            if (!in_array($clientIp, $ipWhitelist)) {
                $this->logger->info('===== Identify Controller ===== IP not found');
                echo 'STOP';
                return;
            }
        }
        
        $postData = $_POST;
        $postjson = json_encode($postData, JSON_PRETTY_PRINT);
        
        $this->logger->info('post : '. $postjson);
        
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('doku_transaction');

        $sql = "SELECT * FROM " . $tableName . " where trans_id_merchant = '" . $postData['TRANSIDMERCHANT'] . "'";
        $dokuOrder = $connection->fetchRow($sql);
        
        $additionalParams = "";
        if($dokuOrder['payment_channel_id'] == "0"){
            $additionalParams = " `payment_channel_id` = '".$postData['PAYMENTCHANNEL']."', ";
        }
        
        if (isset($postData['PAYMENTCODE']) && !empty($postData['PAYMENTCODE'])) {
            $additionalParams .= " `va_number` = '" . $postData['PAYMENTCODE'] . "', ";
        }

        $sql = "Update " . $tableName . " SET ".$additionalParams." `updated_at` = 'now()', `identify_params` = '" . $postjson . "' where trans_id_merchant = '" . $postData['TRANSIDMERCHANT'] . "'";
        $connection->query($sql);
        
        $this->logger->info('===== Identify Controller ===== End');
        exit;
    }

}
