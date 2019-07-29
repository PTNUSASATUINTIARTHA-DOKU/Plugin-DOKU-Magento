<?php

namespace Doku\Merchanthosted\Controller\Payment;

use Magento\Sales\Model\Order;
use \Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Doku\Merchanthosted\Model\DokuMerchanthostedConfigProvider;
use Doku\Core\Helper\Data;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Request\Http;
use Doku\Core\Model\GeneralConfiguration;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ChargeMandiriClickpay extends \Magento\Framework\App\Action\Action {

    protected $_pageFactory;
    protected $session;
    protected $order;
    protected $logger;
    protected $resourceConnection;
    protected $config;
    protected $helper;
    protected $sessionFactory;
    protected $httpRequest;
    protected $generalConfiguration;
    protected $storeManagerInterface;
    protected $_timezoneInterface;

    public function __construct(
        Session $session, 
        Order $order, 
        ResourceConnection $resourceConnection, 
        DokuMerchanthostedConfigProvider $config, 
        Data $helper, 
        Context $context, 
        PageFactory $pageFactory,
        LoggerInterface $loggerInterface,
        SessionFactory $sessionFactory,
        Http $httpRequest,
        GeneralConfiguration $_generalConfiguration,
        StoreManagerInterface $_storeManagerInterface,
        TimezoneInterface $timezoneInterface
    ) {
        $this->session = $session;
        $this->logger = $loggerInterface;
        $this->order = $order;
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        $this->helper = $helper;
        $this->_pageFactory = $pageFactory;
        $this->sessionFactory = $sessionFactory;
        $this->httpRequest = $httpRequest;
        $this->generalConfiguration = $_generalConfiguration;
        $this->storeManagerInterface = $_storeManagerInterface;
        $this->_timezoneInterface = $timezoneInterface;
        return parent::__construct($context);
    }
    
    public function execute() {

        $this->logger->info('===== Charge controller charge CC (merchanthosted) ===== Start');

        $this->logger->info('===== Charge controller charge CC (merchanthosted) ===== Find Order');

        $token = $this->httpRequest->getParam('doku_token');
        $pairing_code = $this->httpRequest->getParam('doku_pairing_code');
        $invoice_no = $this->httpRequest->getParam('doku_invoice_no');
        
        $this->logger->info('===== Charge controller ===== Checking done');
        $this->logger->info('===== Charge controller ===== Finding order...');

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('doku_transaction');

        $sql = "SELECT * FROM " . $tableName . " where trans_id_merchant = '" . $invoice_no . "'";

        $dokuOrder = $connection->fetchRow($sql);
        
        $order = $this->order->loadByIncrementId($invoice_no);

        if (!isset($dokuOrder['trans_id_merchant']) || !$order->getId()) {
            $this->logger->info('===== Charge controller ===== Trans ID Merchant not found! in doku_transaction table');
            echo 'STOP';
            return;
        }
        
        $this->logger->info('===== Charge controller ===== Order found');

        $requestParams = json_decode($dokuOrder['request_params'], true);

        $words = $this->helper->doCreateWords(
                array(
                    'amount' => $requestParams['REQUEST']['req_amount'],
                    'invoice' => $invoice_no,
                    'currency' => '360',
                    'pairing_code' => $pairing_code,
                    'token' => $token,
                    'mallid' => $requestParams["MALLID"],
                    'sharedid' => $requestParams["SHAREDID"]
                )
        );
        
        $basket = "";
        foreach ($order->getAllVisibleItems() as $item) {
            $basket .= preg_replace("/[^a-zA-Z0-9\s]/", "", $item->getName()) . ',' . number_format($item->getPrice(), 2, ".", "") . ',' . (int) $item->getQtyOrdered() . ',' .
                    number_format(($item->getPrice() * $item->getQtyOrdered()), 2, ".", "") . ';';
        }

        $billingData = $order->getBillingAddress();
        
        $customer = array(
            'name' => trim($billingData->getFirstname() . " " . $billingData->getLastname()),
            'data_phone' => $billingData->getTelephone(),
            'data_email' => $billingData->getEmail(),
            'data_address' => $billingData->getStreet()
        );
        
        $dataPayment = array(
            'req_mall_id' => $requestParams["MALLID"],
            'req_chain_merchant' => $requestParams['REQUEST']['req_chain_merchant'],
            'req_amount' => $requestParams['REQUEST']['req_amount'],
            'req_words' => $words,
            'req_purchase_amount' => $requestParams['REQUEST']['req_amount'],
            'req_trans_id_merchant' => $invoice_no,
            'req_request_date_time' => $this->_timezoneInterface->date()->format('YmdHis'),
            'req_currency' => '360',
            'req_purchase_currency' => '360',
            'req_session_id' => $invoice_no,
            'req_name' => $customer['name'],
            'req_payment_channel' => 15,
            'req_basket' => $basket,
            'req_email' => $customer['data_email'],
            'req_token_id' => $token
        );
        
        $requestParams['charge'] = $dataPayment;
        
        $result = $this->helper->doPayment($dataPayment);
        
        $requestParams['charge_response'] = $result;
        
        echo json_encode($result);
        
//        if ($result['res_response_code'] == '0000') {
//            echo 'SUCCESS';
//            //success
//        } else {
//            echo 'FAILED';
//            //failed
//        }
        
        $sql = "Update " . $tableName . " SET `updated_at` = 'now()', `order_status` = 'CHARGE', `request_params` = '" . json_encode($requestParams, JSON_PRETTY_PRINT) . "' where trans_id_merchant = '" .$invoice_no . "'";
        $connection->query($sql);

        $this->logger->info('===== Charge controller charge CC (merchanthosted) ===== end');
        
        exit;
    }

}
