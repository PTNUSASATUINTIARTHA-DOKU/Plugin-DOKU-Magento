<?php

namespace Doku\Core\Cron;

use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Doku\Core\Model\GeneralConfiguration;
use Doku\Core\Helper\Data;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Service\InvoiceService;

class Checkstatus {

    protected $session;
    protected $order;
    protected $logger;
    protected $resourceConnection;
    protected $config;
    protected $helper;
    protected $builderInterface;
    protected $_timezone;
    protected $invoiceService;

    public function __construct(
        Session $session, 
        Order $order, 
        ResourceConnection $resourceConnection, 
        GeneralConfiguration $config, 
        Data $helper, 
        LoggerInterface $logger,
        BuilderInterface $builderInterface,
        TimezoneInterface $timezone,
        InvoiceService $_invoiceService
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
    }

    public function execute() {
        $this->logger->info('===== Cron check status ===== Start');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if ($this->config->getCronConfiguration() != 1) {
            $this->logger->info('===== Cron check status ===== End (Configuration Disabled)');
            return false;
        }

        $config = $this->config->getConfig();

        $orders = $this->order->getCollection()
                ->addFieldToFilter('state', 'new');

        foreach ($orders as $order) {
            try {
                $paymentMethod = $order->getPayment()->getMethod();
                if (in_array($paymentMethod, $this->config->getLablePaymentChannel())) {
                    $this->logger->info('===== Check status for order: ' . $order->getIncrementId() . ' ===== Start');
                    
                    $connection = $this->resourceConnection->getConnection();
                    $tableName = $this->resourceConnection->getTableName('doku_transaction');

                    $sql = "SELECT * FROM " . $tableName . " where trans_id_merchant = '" . $order->getIncrementId() . "'";

                    $dokuOrder = $connection->fetchRow($sql);
                    
                    if(!isset($dokuOrder['id']) || empty($dokuOrder['id']) ){
                        $this->logger->info('===== Check status for order: ' . $order->getIncrementId() . ' ===== Doku order not found');
                        continue;
                    }
                    
                    $requestParams = json_decode($dokuOrder['request_params'], true);
                    $mallId = isset($requestParams['MALLID'])?$requestParams['MALLID']:$requestParams['req_mall_id'];
                    $sharedKey = $requestParams['SHAREDID'];
                    
                    $chainMerchantId = "NA";
                    if(isset($requestParams['CHAINMERCHANT'])){
                        $chainMerchantId = $requestParams['CHAINMERCHANT'];
                    } else if(isset($requestParams['req_chain_merchant'])){
                        $chainMerchantId = $requestParams['req_chain_merchant'];
                    } else if(isset($requestParams['REQUEST']['req_chain_merchant'])){
                        $chainMerchantId = $requestParams['REQUEST']['req_chain_merchant'];
                    }
                    

                    $words = $this->helper->doCreateWords(
                            array(
                                'amount' => $dokuOrder['doku_grand_total'],
                                'invoice' => $order->getIncrementId(),
                                'mallid' => $mallId,
                                'sharedid' => $sharedKey,
                                'check_status' => 1
                            )
                    );

                    $dataParam = [
                        'MALLID' => $mallId,
                        'CHAINMERCHANT' => $chainMerchantId,
                        'TRANSIDMERCHANT' => $order->getIncrementId(),
                        'SESSIONID' => $order->getIncrementId(),
                        'WORDS' => $words,
                        'CURRENCY' => '360',
                        'PURCHASECURRENCY' => '360'
                    ];

                    $this->logger->info('Request parameter : ' . json_encode($dataParam));

                    $result = $this->helper->checkStatusOrder($dataParam);
                    
                    if($result['request_status'] == false){
                        $this->logger->info('===== Check status for order: ' . $order->getIncrementId() . ' ===== Response: '.$result['response'].' End (Response API Error)');
                        continue;
                    }

                    $this->logger->info('Response: ' . json_encode($result));

                    // 0000 => Successful approval
                    if ($result['RESPONSECODE'] == '0000') {
                        
                        if ($this->config->getActiveEdu() == 1) {

                            $paymentChannelsEdu = explode(",", $this->config->getPaymentChanelsEdu());

                            if (in_array($paymentMethod, $paymentChannelsEdu) && $result['EDUSTATUS'] != 'APPROVE') {
                                $this->logger->info('EDU checking');

                                $order->setData('state', 'new');
                                $order->setStatus("waiting_for_verification");
                                $order->save();

                                $this->logger->info('Forward order to EDU checking');
                                continue;              
                            }
                        }
                        
                        $this->logger->info('Create invoice => started...');
                        // create processing status and invoice

                        if ($order->canInvoice() && !$order->hasInvoices()) {
                            $this->logger->info('Create invoice => Proceed...');
                            
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

                            $this->logger->info('Step transactionSave => done');

                            $payment = $order->getPayment();
                            $payment->setLastTransactionId($order->getIncrementId());
                            $payment->setTransactionId($order->getIncrementId());
                            $payment->setAdditionalInformation(array());
                            $message = __('Created by Cron Check Status');
                            $trans = $this->builderInterface;
                            $transaction = $trans->setPayment($payment)
                                    ->setOrder($order)
                                    ->setTransactionId($order->getIncrementId())
                                    ->setAdditionalInformation(array())
                                    ->setFailSafe(true)
                                    ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
                            $payment->addTransactionCommentsToOrder($transaction, $message);
                            $payment->save();
                            $transaction->save();

                            $this->logger->info('Step paymentSave => done');

                            if ($invoice && !$invoice->getEmailSent()) {
                                $invoiceSender = $objectManager->get('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
                                $invoiceSender->send($invoice);
                                $order->addRelatedObject($invoice);
                                $order->addStatusHistoryComment(__('Your Invoice for Order ID #%1.', $order->getIncrementId()))
                                        ->setIsCustomerNotified(true);
                                $this->logger->info('Step email sent => done');
                            }

                            $this->logger->info('All create invoicess process => done');
                        } else {
                            $this->logger->info('Invoice did not created: canInvoice status (' . $order->canInvoice() . ') - hasInvoices status (' . $order->hasInvoices() . ')');
                        }

                        $order->setData('state', 'processing');
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

                        $order->save();

                        $this->logger->info('Order status for: ' . $order->getIncrementId() . ' has been changed to processing');

                    } else {
                        
                        if($result['RESPONSECODE'] != '5511'){
                            $this->logger->info('RESPONSECODE: '.$result['RESPONSECODE']);
                            $this->_cancelOrder($order, $result);
                        }
                        
                        $this->logger->info('RESPONSECODE: '.$result['RESPONSECODE'].', Check expiry order for: ' . $order->getIncrementId() . ' Proceed...');

                        $expiryValue = false;
                        
                        if(isset($requestParams['EXPIRYTIME']) && !empty($requestParams['EXPIRYTIME'])){
                            $expiryValue = $requestParams['EXPIRYTIME'];
                        } else if($requestParams['req_expiry_time'] && !empty($requestParams['EXPIRYTIME'])){
                            $expiryValue = $requestParams['req_expiry_time'];
                        }
                        
                        if(!$expiryValue){
                             continue;
                        }

                        $orderCreated = $this->_timezone->date(new \DateTime($order->getCreatedAt()));
                        $orderCreatedFormatted = $orderCreated->format('Y-m-d H:i:s');

                        $now = $this->_timezone->date()->format('Y-m-d H:i:s');
                        $diffCreatedAt = ceil((strtotime($now) - strtotime($orderCreatedFormatted)) / 60);

                        $this->logger->info('RESPONSECODE: '.$result['RESPONSECODE'].' Check expiry order for: ' . $order->getIncrementId() . ' order_created_at:' . $orderCreatedFormatted . ' Done');

                        if ($expiryValue > $diffCreatedAt) {
                            continue;
                        }

                        $this->logger->info('RESPONSECODE: '.$result['RESPONSECODE'].' Order: ' . $order->getIncrementId() . ' Expired ');

                        $this->_cancelOrder($order, $result);
                    }
                    $this->logger->info('===== Check status for order:' . $order->getIncrementId() . ' ===== End');
                }
            } catch (\Exception $e) {
                $this->logger->info('===== Check status for order:' . $order->getIncrementId() . ' ===== Failed ' . $e->getMessage());
            }
        }
    }
    
    private function _cancelOrder($order, $result)
    {
        $this->logger->info('Cancel order for: ' . $order->getIncrementId() . ' Proceed...');

        $order->cancel();
        $order
            ->addStatusHistoryComment('Automatically Canceled by Doku, Response : ' . json_encode($result))
            ->setIsCustomerNotified(false)->setEntityName('order');
        $order->save();

        // force status order to cancel
        if ($order->getStatus() != 'canceled') {
            $order->setState('canceled');
            $order->setStatus('canceled');
            $order->save();
            $this->logger->info('Force CANCELED order status: ' . $order->getIncrementId());
        }

        $this->logger->info('Cancel order for: ' . $order->getIncrementId() . ' Done');
    }
    
    
}
    
    

