<?php

namespace Doku\Hosted\Controller\Payment;

use Magento\Sales\Model\Order;
use \Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Doku\Hosted\Model\DokuHostedConfigProvider;
use Doku\Core\Helper\Data;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Customer\Model\SessionFactory;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Doku\Core\Model\GeneralConfiguration;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Doku\Core\Model\RecurringFactory;
use Doku\Core\Api\RecurringRepositoryInterface;



class Request extends \Magento\Framework\App\Action\Action {

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
    protected $_timezoneInterface;
    protected $_recurringFactory;
    protected $_recurringRepository;

    public function __construct(
        Session $session, 
        Order $order, 
        ResourceConnection $resourceConnection, 
        DokuHostedConfigProvider $config, 
        Data $helper, 
        Context $context, 
        PageFactory $pageFactory,
        LoggerInterface $loggerInterface,
        SessionFactory $sessionFactory,
        Http $httpRequest,
        GeneralConfiguration $_generalConfiguration,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezoneInterface,
        RecurringFactory $recurringFactory,
        RecurringRepositoryInterface $recurringRepository
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
        $this->scopeConfig = $scopeConfig;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_recurringFactory = $recurringFactory;
        $this->_recurringRepository = $recurringRepository;
        return parent::__construct($context);
    }
    
    protected function getOrder() {

        if (!$this->session->getLastRealOrder()->getIncrementId()) {

            $order = $this->order->getCollection()
                    ->addFieldToFilter('quote_id', $this->session->getQuote()->getId())
                    ->getFirstItem();

            if (!$order->getEntityId()) {
                $customerSession = $this->sessionFactory->create();
                $customerData = $customerSession->getCustomer();
                $order = $this->order->getCollection()
                        ->addFieldToFilter('customer_id', $customerData->getEntityId())
                        ->setOrder('created_at', 'DESC')
                        ->getFirstItem();
            }
            
            return $order;
        } else { 
            return $this->order->loadByIncrementId($this->session->getLastRealOrder()->getIncrementId());
        }
    }

    public function execute() {
        $this->logger->info('===== Request controller (Hosted) ===== Start');

        $this->logger->info('===== Request controller (Hosted) ===== Find Order');

        $result = array();

        $order = $this->getOrder();

        if ($order->getEntityId()) {
            $order->setState(Order::STATE_NEW);
            $this->session->getLastRealOrder()->setState(Order::STATE_NEW);
            $order->save();
            
            $this->logger->info('===== Request controller (Hosted) ===== Order Found!');

            $configCode = $this->config->getRelationPaymentChannel($order->getPayment()->getMethod());

            $billingData = $order->getBillingAddress();
            $config = $this->config->getAllConfig();

            if($order->getPayment()->getMethod() == \Doku\Hosted\Model\Payment\DokuHostedPayment::CODE && $config['payment'][$order->getPayment()->getMethod()]['is_opt_dropdown']) {
                $dokuChannel = $this->httpRequest->getParam('channel');
                if(!empty($dokuChannel)) {
                    $configCode = $dokuChannel;
                }
            }


            $realGrandTotal = $order->getGrandTotal();

            $totalAdminFeeDisc = $this->helper->getTotalAdminFeeAndDisc(
                    $config['payment'][$order->getPayment()->getMethod()]['admin_fee'],
                    $config['payment'][$order->getPayment()->getMethod()]['admin_fee_type'],
                    $config['payment'][$order->getPayment()->getMethod()]['disc_amount'],
                    $config['payment'][$order->getPayment()->getMethod()]['disc_type'],
                    $realGrandTotal);

            if($order->getPayment()->getMethod() == \Doku\Hosted\Model\Payment\DokuHostedPayment::CODE && $config['payment'][$order->getPayment()->getMethod()]['is_opt_dropdown']) {
                $allchannel = \Doku\Hosted\Model\Config\Source\Allchannel::toArray();
                if($dokuChannel && array_key_exists($dokuChannel, $allchannel)) {
                    $selectedChannelCode = $allchannel[$dokuChannel];
                    $totalAdminFeeDisc = $this->helper->getTotalAdminFeeAndDisc(
                        $config['payment'][$selectedChannelCode]['admin_fee'],
                        $config['payment'][$selectedChannelCode]['admin_fee_type'],
                        $config['payment'][$selectedChannelCode]['disc_amount'],
                        $config['payment'][$selectedChannelCode]['disc_type'],
                        $realGrandTotal);
                }
            }
            
            $grandTotal = $realGrandTotal + $totalAdminFeeDisc['total_admin_fee'];
            
            $buffGrandTotal = $grandTotal - $totalAdminFeeDisc['total_discount'];
            
            $grandTotal = $buffGrandTotal < 10000 ? 10000.00 : number_format($buffGrandTotal, 2, ".", ""); 

            $mallId = $config['payment']['core']['mall_id'];
            $sharedId = $this->config->getSharedKey();
            
            $isInstallmentOrder = false;
            $sellectedInstallmentConfig = array();
            if ($order->getPayment()->getMethod() == 'cc_hosted' &&
                    $config['payment']['core']['installment_activation'] == 1 &&
                    $order->getSubtotal() > $config['payment']['core']['installment_amount_above'] &&
                    $this->httpRequest->getParam('bank') &&
                    $this->httpRequest->getParam('bank') != 'no-installment' &&
                    $this->httpRequest->getParam('tennors')
            ) {
                $isInstallmentOrder = true;
                $sellectedInstallmentConfig = $this->generalConfiguration->getSlectedInstallmentConfiguration($this->httpRequest->getParam('bank'), $this->httpRequest->getParam('tennors'));

                if ($sellectedInstallmentConfig['is_on_us'] == 0) {
                    $sharedId = $this->generalConfiguration->getSharedKeyOffUs();
                    $mallId = $this->generalConfiguration->getMallIdOffUs();
                }
            }

            //$transId = $order->getStoreId() . $order->getIncrementId();
            $transId = $order->getIncrementId();
            if($order->getCustomerId()) {
                $customerId = $order->getCustomerId();
            } else {
                $customerId = $order->getEmail();
            }

            $words = $this->helper->doCreateWords(
                    array(
                        'amount' => $grandTotal,
                        'invoice' => $transId,
                        'mallid' => $mallId,
                        'sharedid' => $sharedId
                    )
            );
            
            $basket = "";
            $productInfo = "";
            foreach ($order->getAllVisibleItems() as $item) {
                $basket .= preg_replace("/[^a-zA-Z0-9\s]/", "", $item->getName()). ',' . number_format($item->getPrice(), 2, ".", "") . ',' . (int) $item->getQtyOrdered() . ',' .
                        number_format(($item->getPrice() * $item->getQtyOrdered()), 2, ".", "") . ';';
                $productInfo .= preg_replace("/[^a-zA-Z0-9\s]/", "", $item->getName(). " ");
            }
            $chainMerchant = $config['payment']['core']['chain_id'] ? $config['payment']['core']['chain_id'] : 'NA';

            $result = array(
                'URL' => $config['payment']['core']['request_url'],
                'MALLID' => $mallId,
                'CHAINMERCHANT' => $chainMerchant,
                'AMOUNT' => $grandTotal,
                'PURCHASEAMOUNT' => $grandTotal,
                'TRANSIDMERCHANT' => $transId,
                'WORDS' => $words,
                'REQUESTDATETIME' => $this->_timezoneInterface->date()->format('YmdHis'),
                'CURRENCY' => '360',
                'PURCHASECURRENCY' => '360',
                'SESSIONID' => $order->getIncrementId(),
                'NAME' => trim($billingData->getFirstname() . " " . $billingData->getLastname()),
                'EMAIL' => $billingData->getEmail(),
                'BASKET' => $basket,
                'MOBILEPHONE' => $billingData->getTelephone()
            );
            
            if($configCode != "0"){
               $result['PAYMENTCHANNEL'] = $configCode;
               
               if($result['PAYMENTCHANNEL'] == "37"){
                    $result['SHIPPING_ZIPCODE'] = $billingData->getPostcode();
                    $result['SHIPPING_CITY'] = $billingData->getCity();
                    $result['SHIPPING_ADDRESS'] = $billingData->getStreet();
                    $result['SHIPPING_COUNTRY'] = $billingData->getCountryId();
               }


               if($order->getPayment()->getMethod() == \Doku\Hosted\Model\Payment\CreditCardAuthorizationHosted::CODE){
                    $result['PAYMENTTYPE'] = 'AUTHORIZATION';
                    $paymentType = 'AUTHORIZATION';
                    $authorizationStatus = 'pending';
               } else if($order->getPayment()->getMethod() == \Doku\Hosted\Model\Payment\CreditCardHosted::CODE) {
                   $paymentType = 'SALE';
               }

               if ($this->generalConfiguration->getActiveTokenization() == 1 && $result['PAYMENTCHANNEL'] == "15" && $order->getCustomerId()) {
                    $result['PAYMENTCHANNEL'] = "16";
                    $result['CUSTOMERID'] = $customerId;

                    $connection = $this->resourceConnection->getConnection();
                    $tableName = $this->resourceConnection->getTableName('doku_tokenization_account');

                    $sql = "SELECT * FROM " . $tableName . " where customer_id = '" . $order->getCustomerId() . "'";

                    $tokenData = $connection->fetchRow($sql);
                    
                    if(isset($tokenData['token_id']) && !empty($tokenData['token_id'])){
                        $result['TOKENID'] = $tokenData['token_id'];
                    }
                }


                // recurring
                if ($result['PAYMENTCHANNEL'] == "17" && $order->getCustomerId()) {
                    $historyTrans = $this->resourceConnection->getConnection()->fetchRow("SELECT * FROM doku_transaction where customer_email = '".$billingData->getEmail()."' ORDER BY id DESC LIMIT 1");
                    $billNumber = 1;
                    if(isset($historyTrans['recurring_billnumber']) && !empty($historyTrans['recurring_billnumber'])){
                        $billNumber = explode('-', $historyTrans['recurring_billnumber']);
                        $billNumber = end($billNumber) + 1;
                    }

                    $result['CUSTOMERID'] = $order->getCustomerId();
                    $result['PAYMENTCHANNEL'] = "17";
                    $result['BILLNUMBER'] = $transId;//.$billNumber;

                    // check registration
                    $recurRegisSubsribe = $this->resourceConnection->getConnection()->fetchRow(
                        sprintf("SELECT * FROM doku_recurring_registration where customer_id = '%s' AND subscription_status = %s ORDER BY id DESC LIMIT 1", $billingData->getEmail(), 1)
                    );

                    // already regis & subscribe
                    if (isset($recurRegisSubsribe['customer_id']) && !empty($recurRegisSubsribe['customer_id'])) {
                        $result['URL'] = $this->generalConfiguration->getURLRecurringUpdate();
                        $result['WORDS'] = sha1(
                            $mallId . $chainMerchant . $result['BILLNUMBER'] . $result['CUSTOMERID'] . $sharedId
                        );
                        unset($result['BASKET']);
                        unset($result['NAME']);
                        unset($result['EMAIL']);
                        unset($result['PURCHASEAMOUNT']);
                        unset($result['PURCHASECURRENCY']);
                        unset($result['MOBILEPHONE']);
                        unset($result['CURRENCY']);
                        unset($result['AMOUNT']);
                    } else {
                        // new regis
                        $result['URL'] = $this->generalConfiguration->getURLRecurringReg();
                        $result['WORDS'] = sha1($mallId . $chainMerchant . $result['BILLNUMBER'] . $result['CUSTOMERID'] . $result['AMOUNT'] . $sharedId);
                        $result['BILLDETAIL'] = $productInfo;
                        $result['BILLTYPE'] = "S"; // S = Shopping, I = Installment, D = Donation, P = Payment
                        $recurringStartDate = $this->_timezoneInterface->date()->format('Ymd');
                        $dtRecurringStartDate = $this->_timezoneInterface->date()->format('Y-m-d');
                        $result['STARTDATE'] = $recurringStartDate;
                        $recurringEndDate = $this->_timezoneInterface->date(strtotime($result['STARTDATE'].' +5 years'))->format('Ymd');
                        $dtRecurringEndDate = $this->_timezoneInterface->date(strtotime($result['STARTDATE'].' +5 years'))->format('Y-m-d');
                        $result['ENDDATE'] = $recurringEndDate;
                        $result['EXECUTETYPE'] = 'DATE';
                        $result['EXECUTEDATE'] = $this->generalConfiguration->getRecurringExecutedate();
                        $result['EXECUTEMONTH'] = $this->generalConfiguration->getRecurringExecutemonth();
                        $result['FLATSTATUS'] = $this->generalConfiguration->getRecurringFlatstatus() ? 'TRUE' : 'FALSE';
                        if ($this->generalConfiguration->getRecurringRegisteramount()) {
                            $result['REGISTERAMOUNT'] = $this->generalConfiguration->getRecurringRegisteramount();
                        }
                    }
                }
            }

            if($isInstallmentOrder){
                $result['PROMOID'] = $sellectedInstallmentConfig['promo_id'];
                $result['TENOR'] = $sellectedInstallmentConfig['tennor'];
                $result['INSTALLMENT_ACQUIRER'] = $sellectedInstallmentConfig['installment_acquierer_code'];
                $result['PAYMENTTYPE'] = $sellectedInstallmentConfig['is_on_us'] == 0?'OFFUSINSTALLMENT':'';
            }

            $jsonResult = json_encode(array_merge($result, array('SHAREDID' => $sharedId, 'EXPIRYTIME' => $config['payment']['core']['expiry'])), JSON_PRETTY_PRINT);
            $this->logger->info('parameter : ' . $jsonResult);

            $dokuTransData = [
                'quote_id' => $order->getQuoteId(),
                'store_id' => $order->getStoreId(),
                'order_id' => $order->getId(),
                'trans_id_merchant' => $order->getIncrementId(),
                'payment_channel_id' => $configCode,
                'order_status' => 'REQUEST',
                'request_params' => $jsonResult,
                'created_at' => 'now()',
                'updated_at' => 'now()',
                'doku_grand_total' => $grandTotal,
                'admin_fee_type' => $config['payment'][$order->getPayment()->getMethod()]['admin_fee_type'],
                'admin_fee_amount' => $config['payment'][$order->getPayment()->getMethod()]['admin_fee'],
                'admin_fee_trx_amount' => $totalAdminFeeDisc['total_admin_fee'],
                'discount_type' => $config['payment'][$order->getPayment()->getMethod()]['disc_type'],
                'discount_amount' => $config['payment'][$order->getPayment()->getMethod()]['disc_amount'],
                'discount_trx_amount' => $totalAdminFeeDisc['total_discount'],
                'customer_email' => isset($result['CUSTOMERID']) ? $result['CUSTOMERID']: '',
                'recurring_billnumber' => isset($result['BILLNUMBER']) ? $result['BILLNUMBER'] : '',
                'recurring_flatstatus' => isset($result['FLATSTATUS']) ? $result['FLATSTATUS'] : null,
            ];

            if(!empty($paymentType)) {
                $dokuTransData['payment_type'] = $paymentType;
            }

            if(isset($authorizationStatus)) {
                $dokuTransData['authorization_status'] = $authorizationStatus;
            }

            if($order->getPayment()->getMethod() == \Doku\Hosted\Model\Payment\CreditCardAuthorizationHosted::CODE){
                /* set auth expiry */
                $expiry = $this->scopeConfig->getValue('payment/cc_authorization_hosted/auth_time_expiry', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if(!$expiry) {
                    $expiry = 60;
                }

                $expSeconds = $expiry * 60;
                $expiryDate = $this->_timezoneInterface->date(new \DateTime($order->getCreatedAt()));
                $expiryDate->add(new \DateInterval("PT". $expSeconds."S"));
                $expiryDate->add(new \DateInterval("PT7H"));
                $dokuTransData['auth_expired'] = $expiryDate->format('Y-m-d H:i:s');

            }



            $this->resourceConnection->getConnection()->insert('doku_transaction', $dokuTransData);

        } else {
            $this->logger->info('===== Request controller (Hosted) ===== Order not found');
        }

        $this->logger->info('===== Request controller (Hosted) ===== end');

        if ($configCode == "17" && $order->getCustomerId()) {
            $recurringModel = $this->_recurringFactory->create();
            $recurringData = array(
                'customer_id' => $result['CUSTOMERID'],
                'status_type' => 'G', //registration
                'status' => 'PENDING',
                'bill_number' => $result['BILLNUMBER'],
                'bill_type' => $result['BILLTYPE'],
                'start_date' => $dtRecurringStartDate,
                'end_date' => $dtRecurringEndDate,
                'execute_type' => $result['EXECUTETYPE'],
                'execute_date' => $result['EXECUTEDATE'],
                'execute_month' => $result['EXECUTEMONTH'],
                'flat_status' => $result['FLATSTATUS'],
                'subscription_status' => 1
            );
            $recurringModel->setData($recurringData);

            /* Direct entity save is deprecated, use service contract instead */
            //$recurringModel->save();
            $this->_recurringRepository->save($recurringModel);
        }

        echo json_encode(array('err' => false, 'response_msg' => 'Generate Form Success',
                    'result' => $result));
    }

}
