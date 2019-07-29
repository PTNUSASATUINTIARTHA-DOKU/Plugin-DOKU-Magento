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
use \Magento\Framework\View\Asset\Repository;

class RequestMandiriClickpay extends \Magento\Framework\App\Action\Action {

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
    protected $assetRepo;
    protected $requestObj;

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
        Repository $_assetRepo
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
        $this->assetRepo = $_assetRepo;
        $this->requestObj = $context->getRequest();
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

        $this->logger->info('===== Request controller request CC (merchanthosted) ===== Start');

        $this->logger->info('===== Request controller request CC (merchanthosted) ===== Find Order');

        $order = $this->getOrder();

        if ($order->getEntityId()) {
            $order->setState(Order::STATE_NEW);
            $this->session->getLastRealOrder()->setState(Order::STATE_NEW);
            $order->save();

            $this->logger->info('===== Request controller request CC (merchanthosted) ===== Order Found!');

            $configCode = $this->config->getRelationPaymentChannel($order->getPayment()->getMethod());

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

            $base_url = $this->storeManagerInterface
                        ->getStore($order->getStore()->getId())
                        ->getBaseUrl();

//            $isSecureParams = array('_secure' => $this->requestObj->isSecure());
//            $doku_js_url = $this->assetRepo->getUrl("Doku_Merchanthosted::js/doku/doku-1.3.js", $isSecureParams);
            
            $doku_js_url = $this->generalConfiguration->getJsMechantHosted();
            
            $params = array(
                'req_merchant_code' => $mallId,
                'req_chain_merchant' => $config['payment']['core']['chain_id'] ? $config['payment']['core']['chain_id'] : 'NA',
                'req_payment_channel' => $configCode,
                'req_transaction_id' => $order->getIncrementId(),
                'req_currency' => '360',
                'req_amount' => $grandTotal,
                'req_words' => $words,
                'req_form_type' => 'full',
                'req_server_url' =>  $base_url."dokumerchanthosted/payment/chargemandiriclickpay",
                'cc_number' => 'cc_number',
                'CHALLENGE_CODE_1' => 'CHALLENGE_CODE_1',
                'CHALLENGE_CODE_2' => ceil($grandTotal),
                'doku_js_url' => $doku_js_url
                
            );

            $this->logger->info('===== Request controller request CC (merchanthosted) ===== request payment = ' . json_encode($params, JSON_PRETTY_PRINT));

            $dataLogParams = array(
                'MALLID' => $mallId,
                'SHAREDID' => $sharedId,
                'REQUEST' => $params
            );

            $this->resourceConnection->getConnection()->insert('doku_transaction', [
                'quote_id' => $order->getQuoteId(),
                'store_id' => $order->getStoreId(),
                'order_id' => $order->getId(),
                'trans_id_merchant' => $order->getIncrementId(),
                'payment_channel_id' => $configCode,
                'order_status' => 'REQUEST',
                'request_params' => json_encode($dataLogParams, JSON_PRETTY_PRINT),
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

            echo json_encode(array('err' => false, 'response_msg' => 'Generate request Success',
                'result' => $params));
        } else {
            $this->logger->info('===== Request controller request CC (merchanthosted) ===== Order not found');
            
            echo json_encode(array('err' => true, 'response_msg' => 'Generate request failed (order not found}',
                'result' => array()));
        }

        $this->logger->info('===== Request controller request CC (merchanthosted) ===== end');
    }

}
