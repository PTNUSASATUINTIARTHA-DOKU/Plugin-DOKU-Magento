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

class RequestVa extends \Magento\Framework\App\Action\Action {

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

        $this->logger->info('===== Request controller VA (merchanthosted) ===== Start');

        $this->logger->info('===== Request controller VA (merchanthosted) ===== Find Order');

        $result = array();
        $redrectData = array();
        
        $order = $this->getOrder();

        if ($order->getEntityId()) {
            $order->setState(Order::STATE_NEW);
            $this->session->getLastRealOrder()->setState(Order::STATE_NEW);
            $order->save();
            
            $this->logger->info('===== Request controller VA (merchanthosted) ===== Order Found!');

            $configCode = $this->config->getRelationPaymentChannel($order->getPayment()->getMethod());

            $billingData = $order->getBillingAddress();
            $config = $this->config->getAllConfig();
            
            $realGrandTotal = $order->getGrandTotal();

            $totalAdminFeeDisc = $this->helper->getTotalAdminFeeAndDisc(
                    $config['payment'][$order->getPayment()->getMethod()]['admin_fee'], 
                    $config['payment'][$order->getPayment()->getMethod()]['admin_fee_type'],
                    $config['payment'][$order->getPayment()->getMethod()]['disc_amount'], 
                    $config['payment'][$order->getPayment()->getMethod()]['disc_type'],
                    $realGrandTotal);
            
            $grandTotal = $realGrandTotal + $totalAdminFeeDisc['total_admin_fee'];
            
            $buffGrandTotal = $grandTotal - $totalAdminFeeDisc['total_discount'];
            
            $grandTotal = $buffGrandTotal < 10000 ? 10000.00 : number_format($buffGrandTotal, 2, ".", ""); 
            
            $mallId = $config['payment']['core']['mall_id'];
            $sharedId = $this->config->getSharedKey();

            $words = $this->helper->doCreateWords(
                    array(
                        'mallid' => $mallId,
                        'sharedid' => $sharedId,
                        'amount' => $grandTotal,
                        'invoice' => $order->getIncrementId(),
                        'currency' => '360',
                    )
            );

            $basket = "";
            foreach ($order->getAllVisibleItems() as $item) {
                $basket .= preg_replace("/[^a-zA-Z0-9\s]/", "", $item->getName()). ',' . number_format($item->getPrice(), 2, ".", "") . ',' . (int) $item->getQtyOrdered() . ',' .
                        number_format(($item->getPrice() * $item->getQtyOrdered()), 2, ".", "") . ';';
            }
            
            $params = array(
                'req_mall_id' => $mallId,
                'req_chain_merchant' => $config['payment']['core']['chain_id'] ? $config['payment']['core']['chain_id'] : 'NA',
                'req_amount' => $grandTotal,
                'req_words' => $words,
                'req_purchase_amount' => $grandTotal,
                'req_trans_id_merchant' => $order->getIncrementId(),
                'req_request_date_time' => $this->_timezoneInterface->date()->format('YmdHis'),
                'req_session_id' => $order->getIncrementId(),
                'req_name' => trim($billingData->getFirstname() . " " . $billingData->getLastname()),
                'req_email' => $billingData->getEmail(),
                'req_basket' => $basket,
                'req_expiry_time' => isset($config['payment']['core']['expiry']) && (int) $config['payment']['core']['expiry'] != 0 ?  $config['payment']['core']['expiry']:360,
                'req_address' => preg_replace("/[^a-zA-Z0-9\s]/", "", implode(" ",$billingData->getStreet()).", ".$billingData->getCity()),
                'req_mobile_phone' => $billingData->getTelephone()
            );

            $this->logger->info('===== Request controller VA (merchanthosted) ===== request param = '. json_encode($params, JSON_PRETTY_PRINT));
            $this->logger->info('===== Request controller VA (merchanthosted) ===== send request');
            
            $orderStatus = 'FAILED';
            try {
                $result = $this->helper->doGeneratePaycode($params);
            } catch(\Exception $e) {
                $result['res_response_code'] = "500";
                $result['res_response_msg'] = "Can't connect to server";
            }
            
            $this->logger->info('===== Request controller VA (merchanthosted) ===== response payment = '. json_encode($result, JSON_PRETTY_PRINT));
            
            if($result['res_response_code'] == '0000'){
                $orderStatus = 'PENDING';
            }
            
            $params['SHAREDID'] = $sharedId;
            $params['RESPONSE'] = $result;
            
            $jsonResult = json_encode(array_merge($params), JSON_PRETTY_PRINT);
            
            $vaNumber = '';
            if(isset($result['res_pay_code'])){
                $vaNumber = $this->config->getPaymentCodePrefix($order->getPayment()->getMethod()).$result['res_pay_code'];
            }
            
            $this->resourceConnection->getConnection()->insert('doku_transaction', [
                    'quote_id' => $order->getQuoteId(),
                    'store_id' => $order->getStoreId(),
                    'order_id' => $order->getId(),
                    'trans_id_merchant' => $order->getIncrementId(),
                    'payment_channel_id' => $configCode,
                    'order_status' => $orderStatus,
                    'request_params' => $jsonResult,
                    'va_number' => $vaNumber,
                    'created_at' => 'now()',
                    'updated_at' => 'now()',
                    'doku_grand_total' => $grandTotal,
                    'admin_fee_type' => $config['payment'][$order->getPayment()->getMethod()]['admin_fee_type'],
                    'admin_fee_amount' => $config['payment'][$order->getPayment()->getMethod()]['admin_fee'],
                    'admin_fee_trx_amount' => $totalAdminFeeDisc['total_admin_fee'],
                    'discount_type' => $config['payment'][$order->getPayment()->getMethod()]['disc_type'],
                    'discount_amount' => $config['payment'][$order->getPayment()->getMethod()]['disc_amount'],
                    'discount_trx_amount' => $totalAdminFeeDisc['total_discount']
                ]);
            
            $base_url = $this->storeManagerInterface
                ->getStore($order->getStore()->getId())
                ->getBaseUrl();

            $redrectData['URL'] = $base_url . "dokucore/service/redirect";
            $redrectData['RESPONSECODE'] = $result['res_response_code'];
            $redrectData['RESPONSEMSG'] = $result['res_response_msg'];
            $redrectData['TRANSIDMERCHANT'] = $order->getIncrementId();

            $wordsParams = array(
                'amount' => number_format($order->getGrandTotal(),2,".",""),
                'sharedid' => $sharedId,
                'invoice' => $order->getIncrementId(),
                'statuscode' => $result['res_response_code']
            );            
             
            $redirectWords = $this->helper->doCreateWords($wordsParams);

            $redrectData['WORDS'] = $redirectWords;
            $redrectData['STATUSCODE'] = $result['res_response_code'];
            
        } else {
            $this->logger->info('===== Request controller VA (merchanthosted) ===== Order not found');
        }

        $this->logger->info('===== Request controller VA (merchanthosted) ===== end');
        
        if ($result['res_response_code'] == '0000') {
            echo json_encode(array('err' => false, 'response_msg' => 'Generate paycode Success',
                'result' => $redrectData));
        } else {
              echo json_encode(array('err' => true, 'response_msg' => 'Generate paycode failed (error code: '.$result['res_response_code'].'}',
                'result' => $redrectData));
        }

    }

}
