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
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;

class ChargeMerchanthosted extends \Magento\Framework\App\Action\Action {

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
    protected $invoiceService;
    protected $builderInterface;

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
        TimezoneInterface $timezoneInterface,
        InvoiceService $_invoiceService,
        BuilderInterface $_builderInterface
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
        $this->invoiceService = $_invoiceService;
        $this->builderInterface = $_builderInterface;
        return parent::__construct($context);
    }
    
    public function execute() {

        $this->logger->info('===== Charge controller  (merchanthosted) ===== Start');

        $this->logger->info('===== Charge controller  (merchanthosted) ===== Find Order');

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
        
        $configCode = $this->config->getRelationPaymentChannel($order->getPayment()->getMethod());
        
        $this->logger->info('===== Charge controller ===== Order found');

        $requestParams = json_decode($dokuOrder['request_params'], true);
        
        $wordsRequestParams = array(
            'amount' => $requestParams['REQUEST']['req_amount'],
            'invoice' => $invoice_no,
            'currency' => '360',
            'mallid' => $requestParams["MALLID"],
            'sharedid' => $requestParams["SHAREDID"]
        );

        if ($pairing_code) {
            $wordsRequestParams['pairing_code'] = $pairing_code;
        }

        if ($token) {
            $wordsRequestParams['token'] = $token;
        }

        $words = $this->helper->doCreateWords($wordsRequestParams);

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
            'data_address' => implode(" ", $billingData->getStreet())
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
            'req_payment_channel' => $configCode,
            'req_basket' => $basket,
            'req_email' => $customer['data_email'],
            'req_token_id' => $token,
            'req_mobile_phone' => $customer['data_phone'],
            'req_address' => $customer['data_address']
        );
        
        if ($configCode == '02') { //If payment mandiri clickpay			
            $dataPayment['req_card_number'] = str_replace(" - ", "", $this->httpRequest->getParam('cc_number'));
            $dataPayment['req_challenge_code_1'] = $this->httpRequest->getParam('CHALLENGE_CODE_1');
            $dataPayment['req_challenge_code_2'] = $this->httpRequest->getParam('CHALLENGE_CODE_2');
            $dataPayment['req_challenge_code_3'] = $this->httpRequest->getParam('CHALLENGE_CODE_3');
            $dataPayment['req_response_token'] = $this->httpRequest->getParam('response_token');
            unset($dataPayment['req_token_id']);
        }

        $requestParams['charge'] = $dataPayment;

        $result = array();
        
        if ($configCode == '02') {
            $result = $this->helper->doPrePayment($dataPayment);
        } else {
            $result = $this->helper->doPayment($dataPayment);
        }

        $requestParams['charge_response'] = $result;

        $base_url = $this->storeManagerInterface
                ->getStore($order->getStore()->getId())
                ->getBaseUrl();

        $redirectParams['RESPONSECODE'] = $result['res_response_code'];
        $redirectParams['RESPONSEMSG'] = $result['res_response_msg'];
        $redirectParams['TRANSIDMERCHANT'] = $order->getIncrementId();
        
        if (empty($result) || !isset($result['res_response_code'])) {
            $result = [
                "res_response_msg" => "TIME OUT",
                "res_response_code" => "5536",
                "original_response" => $result
            ];
        }

        $wordsParams = array(
            'amount' => $requestParams['REQUEST']['req_amount'],
            'sharedid' => $requestParams["SHAREDID"],
            'invoice' => $order->getIncrementId(),
            'statuscode' => $result['res_response_code']
        );

        $redirectWords = $this->helper->doCreateWords($wordsParams);

        $redirectParams['WORDS'] = $redirectWords;
        $redirectParams['STATUSCODE'] = $result['res_response_code'];

        $redirectUrl = $base_url . "dokucore/service/redirect?" . http_build_query($redirectParams);

        $result['res_redirect_url'] = $redirectUrl;
        
        $eduStatus = false;
        
        $statusLabel = 'CHARGE';
        
        if ($result['res_response_code'] == '0000') {

            $this->logger->info('===== Notify Controller ===== Checking EDU');
            $paymentMethod = $order->getPayment()->getMethod();
            if ($this->generalConfiguration->getActiveEdu() == 1) {
                $paymentChannelsEdu = explode(",", $this->generalConfiguration->getPaymentChanelsEdu());

                if (in_array($paymentMethod, $paymentChannelsEdu)) {

                    $order->setData('state', 'new');
                    $order->setStatus("waiting_for_verification");
                    $order->save();

                    $this->logger->info('===== Notify Controller ===== Forward order to EDU checking');
                    $eduStatus = true;
                    
                    $statusLabel = 'REVIEW';
                }
            }

            if (!$eduStatus) {
                if ($order->canInvoice() && !$order->hasInvoices()) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->register();
                    $invoice->pay();
                    $invoice->save();
                    $transactionSave = $objectManager->create(
                                    'Magento\Framework\DB\Transaction'
                            )->addObject(
                                    $invoice
                            )->addObject(
                            $invoice->getOrder()
                    );
                    $transactionSave->save();

                    $payment = $order->getPayment();
                    $payment->setLastTransactionId($invoice_no?$invoice_no:$order->getIncrementId());
                    $payment->setTransactionId($invoice_no?$invoice_no:$order->getIncrementId());
                    $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $_POST]);
                    $message = __(json_encode($_POST, JSON_PRETTY_PRINT));
                    $trans = $this->builderInterface;
                    $transaction = $trans->setPayment($payment)
                            ->setOrder($order)
                            ->setTransactionId($invoice_no?$invoice_no:$order->getIncrementId())
                            ->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $_POST])
                            ->setFailSafe(true)
                            ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
                    $payment->addTransactionCommentsToOrder($transaction, $message);
                    $payment->save();
                    $transaction->save();

                    if ($invoice && !$invoice->getEmailSent()) {
                        $invoiceSender = $objectManager->get('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
                        $invoiceSender->send($invoice);
                        $order->addRelatedObject($invoice);
                        $order->addStatusHistoryComment(__('Your Invoice for Order ID #%1.', $invoice_no?$invoice_no:$order->getIncrementId()))
                                ->setIsCustomerNotified(true);
                    }
                }

                $order->setData('state', 'processing');
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

                $order->save();
            }
            
            $statusLabel = 'SUCCESS';
        }
        
        
        
        $sql = "Update " . $tableName . " SET `updated_at` = 'now()', `order_status` = '".$statusLabel."', `request_params` = '" . json_encode($requestParams, JSON_PRETTY_PRINT) . "' where trans_id_merchant = '" .$invoice_no . "'";
        $connection->query($sql);

        $this->logger->info('===== Charge controller  (merchanthosted) ===== end');
        
        if($this->httpRequest->getParam('is_not_ajax')){
            header("Location: ".$result['res_redirect_url']);
        } else {
            echo json_encode($result);
        }
        
        exit;
    }

}
