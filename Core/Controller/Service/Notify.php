<?php
namespace Doku\Core\Controller\Service;

use Doku\Core\Model\RecurringpaymentFactory;
use \Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Service\InvoiceService;
use \Psr\Log\LoggerInterface;
use Doku\Core\Model\GeneralConfiguration;
use Doku\Core\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Doku\Core\Api\RecurringRepositoryInterface;
use Doku\Core\Api\TransactionRepositoryInterface;
use Doku\Core\Api\RecurringpaymentRepositoryInterface;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Notify extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface {

    const ORDER_STATUS_CHALLENGE = 'challenge';

    protected $resourceConnection;
    protected $order;
    protected $generalConfiguration;
    protected $logger;
    protected $invoiceService;
    protected $builderInterface;
    protected $coreHelper;
    protected $timezoneInterface;
    protected $transactionRepository;
    protected $recurringRepository;
    protected $recurringpaymentRepository;

    public function __construct(
        LoggerInterface $loggerInterface,
        Context $context,
        ResourceConnection $resourceConnection,
        Order $order,
        BuilderInterface $_builderInterface,
        InvoiceService $_invoiceService,
        GeneralConfiguration $_generalConfiguration,
        Data $_coreHelper,
        TimezoneInterface $timezoneInterface,
        TransactionRepositoryInterface $transactionRepository,
        RecurringRepositoryInterface $recurringRepository,
        RecurringpaymentRepositoryInterface $recurringpaymentRepository,
        RecurringpaymentFactory $recurringpaymentFactory
    )
    {
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
        $this->transactionRepository = $transactionRepository;
        $this->recurringRepository = $recurringRepository;
        $this->recurringpaymentRepository = $recurringpaymentRepository;
        $this->recurringpaymentFactory = $recurringpaymentFactory;
    }

    public function execute()
    {
        $this->logger->info('===== Notify Controller ===== Start');

        try{
            
            $this->logger->info('===== Notify Controller ===== Checking whitelist IP');
            
            if (!empty($this->generalConfiguration->getIpWhitelist())) {
                $ipWhitelist = explode(",", $this->generalConfiguration->getIpWhitelist());

                $clientIp = $this->coreHelper->getClientIp();

                if (!in_array($clientIp, $ipWhitelist)) {
                    $this->logger->info('===== Notify Controller ===== IP not found');
                    echo 'STOP';
                    return;
                }
            }

            $parsedRaw = array();
            $rawbody = urldecode(file_get_contents('php://input'));
            parse_str($rawbody, $parsedRaw);

            $this->logger->info('NOTIFY RAW PARAMS : '. json_encode($parsedRaw));

            $postjson = json_encode($_POST, JSON_PRETTY_PRINT);

            $this->logger->info('NOTIFY PARAMS : '. $postjson);

            $postData = $_POST;
            
            $this->logger->info('===== Notify Controller ===== Checking done');
            $this->logger->info('===== Notify Controller ===== Finding order...');

            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('doku_transaction');


            // reg notify recurring
            $isRecurring = false;
            if (isset($postData['BILLNUMBER']) && !empty($postData['BILLNUMBER'])) {
                $isRecurring = true;
                $postData['TRANSIDMERCHANT'] = $postData['BILLNUMBER'];
                $order = $this->order->loadByIncrementId($postData['BILLNUMBER']);


                if (!$order->getId()) {
                    $this->logger->info('===== Notify Controller ===== Order not found!');
                    echo 'STOP';
                    return;
                }

                // G: Notify Registration T: Notify Update
                if ($postData['STATUSTYPE'] == 'G') {
                    $this->recurringRegistration($postData);
		            //return;
                } else {
                    // update recurring
                    $this->recurringUpdate($postData);
		            //return;
                }
            } else {
                $order = $this->order->loadByIncrementId($postData['TRANSIDMERCHANT']);
                if (!$order->getId()) {
                    $this->logger->info('===== Notify Controller ===== Order not found!');
                    echo 'STOP';
                    return;
                }
            }

            $sql = "SELECT * FROM " . $tableName . " where trans_id_merchant = '" . $postData['TRANSIDMERCHANT'] . "'";

            $dokuOrder = $connection->fetchRow($sql);
            $_dokuTrans = $this->transactionRepository->getByTransIdMerchant($postData['TRANSIDMERCHANT']);
            
            if(!isset($dokuOrder['trans_id_merchant'])){
                $this->logger->info('===== Notify Controller ===== Trans ID Merchant not found! in doku_transaction table');
                echo 'STOP';
                return;
            }

            $this->logger->info('===== Notify Controller ===== Order found');
            $this->logger->info('===== Notify Controller ===== Updating order...');


            if($isRecurring) {
                $mallId = $this->generalConfiguration->getMallId();
                $sharedKey = $this->generalConfiguration->getSharedKey();
                $chainMerchant = $this->generalConfiguration->getChainId();

                $words = sha1($mallId . $chainMerchant . $postData['BILLNUMBER'] . $postData['CUSTOMERID'] . $postData['STATUS'] .  $sharedKey);

                $this->logger->info('words : '. $words);
                $this->logger->info('===== Notify Controller ===== Checking words...');

                if ($postData['WORDS'] != $words) {
                    $this->logger->info('===== Notify Controller ===== Words not match!');
                    echo 'STOP';
                    return;
                }
            } else {
                $requestParams = json_decode($dokuOrder['request_params'], true);
                $mallId = isset($requestParams['MALLID'])?$requestParams['MALLID']:$requestParams['req_mall_id'];
                $sharedKey = $requestParams['SHAREDID'];

                $words = sha1($postData['AMOUNT'] . $mallId . $sharedKey
                    . $postData['TRANSIDMERCHANT'] . $postData['RESULTMSG'] . $postData['VERIFYSTATUS']);

                $this->logger->info('words raw : '. $postData['AMOUNT'] . $mallId . $sharedKey
                    . $postData['TRANSIDMERCHANT'] . $postData['RESULTMSG'] . $postData['VERIFYSTATUS']);
                $this->logger->info('words : '. $words);
                $this->logger->info('===== Notify Controller ===== Checking words...');

                if ($postData['WORDS'] != $words) {
                    $this->logger->info('===== Notify Controller ===== Words not match!');
                    echo 'STOP';
                    return;
                }

                if ($postData['RESPONSECODE'] != '0000') {
                    $this->logger->info('===== Notify Controller ===== RESULTMSG is not success!');

                    $sql = "Update " . $tableName . " SET `updated_at` = 'now()', `order_status` = '".$postData['RESULTMSG']."' , `notify_params` = '" . $postjson . "' where trans_id_merchant = '" . $postData['TRANSIDMERCHANT'] . "'";
                    $connection->query($sql);

                    echo 'CONTINUE';
                    return;
                }

            }
            

            $this->logger->info('===== Notify Controller ===== Checking EDU');
            $paymentMethod = $order->getPayment()->getMethod();
            if ($this->generalConfiguration->getActiveEdu() == 1) {
                $paymentChannelsEdu = explode(",", $this->generalConfiguration->getPaymentChanelsEdu());

                if (in_array($paymentMethod, $paymentChannelsEdu)) {
                    $sql = "Update " . $tableName . " SET `updated_at` = 'now()', `order_status` = 'REVIEW',  `notify_params` = '" . $postjson . "' where trans_id_merchant = '" . $postData['TRANSIDMERCHANT'] . "'";
                    $connection->query($sql);
                    
                    $order->setData('state', 'new');
                    $order->setStatus("waiting_for_verification");
                    $order->save();
                    
                    $this->logger->info('===== Notify Controller ===== Forward order to EDU checking');
                    
                    echo 'CONTINUE';
                    return;
                }
            }

            $_dokuTrans->setApprovalCode($postData['APPROVALCODE']);

            if($order->canInvoice() && !$order->hasInvoices() && $paymentMethod == \Doku\Hosted\Model\Payment\CreditCardAuthorizationHosted::CODE) {
                $_dokuTrans->setApprovalCode($postData['APPROVALCODE']);
                $_dokuTrans->setAuthorizationStatus('authorization');
                $this->transactionRepository->save($_dokuTrans);

                $payment = $order->getPayment();
                $payment->setLastTransactionId($postData['TRANSIDMERCHANT']);
                $payment->setTransactionId($postData['TRANSIDMERCHANT']);
                $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $_POST]);
                $message = __(json_encode($_POST, JSON_PRETTY_PRINT));

                $trans = $this->builderInterface;

                $transactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                $transaction = $trans->setPayment($payment)
                    ->setOrder($order)
                    ->setTransactionId($postData['TRANSIDMERCHANT']. $transactionType)
                    ->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $_POST])
                    ->setFailSafe(true)
                    ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH);
                $payment->addTransactionCommentsToOrder($transaction, $message);
                $payment->save();
                $transaction->save();
                $order->setStatus(self::ORDER_STATUS_CHALLENGE);
                $order->save();

            }

            if ($order->canInvoice() && !$order->hasInvoices() && $paymentMethod != \Doku\Hosted\Model\Payment\CreditCardAuthorizationHosted::CODE) {
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
                $payment->setLastTransactionId($postData['TRANSIDMERCHANT']);
                $payment->setTransactionId($postData['TRANSIDMERCHANT']);
                $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $_POST]);
                $message = __(json_encode($_POST, JSON_PRETTY_PRINT));
                $trans = $this->builderInterface;


                $transactionType = $paymentMethod == \Doku\Hosted\Model\Payment\CreditCardHosted::CODE ? \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER : \Magento\Sales\Model\Order\Payment\Transaction::TYPE_PAYMENT;
                $transaction = $trans->setPayment($payment)
                        ->setOrder($order)
                        ->setTransactionId($postData['TRANSIDMERCHANT'])
                        ->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $_POST])
                        ->setFailSafe(true)
                        ->build($transactionType);
                $payment->addTransactionCommentsToOrder($transaction, $message);
                $payment->save();
                $transaction->save();

                if ($invoice && !$invoice->getEmailSent()) {
                    $invoiceSender = $objectManager->get('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
                    $invoiceSender->send($invoice);
                    $order->addRelatedObject($invoice);
                    $order->addStatusHistoryComment(__('Your Invoice for Order ID #%1.', $postData['TRANSIDMERCHANT']))
                            ->setIsCustomerNotified(true);
                }
                $order->setData('state', 'processing');
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            }

            $order->save();
            
            $sql = "Update " . $tableName . " SET `updated_at` = 'now()', `order_status` = 'SUCCESS' , `notify_params` = '" . $postjson . "' where trans_id_merchant = '" . $postData['TRANSIDMERCHANT'] . "'";
            $connection->query($sql);
            
            if(isset($postData['CUSTOMERID']) && !empty($postData['CUSTOMERID']) && isset($postData['TOKENID']) && !empty($postData['TOKENID'])){
                
                $tableName = $this->resourceConnection->getTableName('doku_tokenization_account');
                $sql = "SELECT * FROM " . $tableName . " where customer_id = '" . $postData['CUSTOMERID'] . "'";
                $dokuToken = $connection->fetchRow($sql);
                
                if(isset($dokuToken['customer_id'])){
                    $sql = "Update " . $tableName . " SET `token_id` = '".$postData['TOKENID']."' where customer_id = '" . $dokuToken['customer_id'] . "'";
                    $connection->query($sql);
                } else {
                    $this->resourceConnection->getConnection()->insert('doku_tokenization_account', [
                    'customer_id' => $postData['CUSTOMERID'],
                    'token_id' => $postData['TOKENID']
                ]);
                }   
            }

            $this->logger->info('===== Notify Controller ===== Updating success...');
            echo 'CONTINUE';

            $this->logger->info('===== Notify Controller ===== End');
            
        }catch(\Exception $e){
            $this->logger->info('===== Notify Controller ===== Generate code error : '. $e->getMessage());
            $this->logger->info('===== Notify Controller ===== End');

            echo 'STOP';
        }

    }

    public  function recurringRegistration($postData) {
        $this->logger->info(get_class($this) . '===== RECURRING REGISTRATION NOTIFY  :: Start');

        $billNumber = $postData['BILLNUMBER'];
        $cardNumber = $postData['CARDNUMBER'];
        $recurringModel = $this->recurringRepository->getByBillNumber($billNumber);

        $mallId = $this->generalConfiguration->getMallId();
        $sharedKey = $this->generalConfiguration->getSharedKey();
        $chainMerchant = $this->generalConfiguration->getChainId();

        //MALLID+CHAINMERCHANT+BILLNUMBER+CUSTOMERID+STATUS +<shared key>.
        $words = sha1($mallId . $chainMerchant
            . $postData['BILLNUMBER'] .$postData['CUSTOMERID']
            . $postData['STATUS'] . $sharedKey);
//        echo $words; die();

        if ($postData['WORDS'] != $words) {
            $this->logger->info(get_class($this) . '===== RECURRING REGISTRATION NOTIFY :: Words not match!');
            return;
        }
        $this->logger->info(get_class($this) . '===== RECURRING REGISTRATION NOTIFY :: Words match!');
        $this->logger->info(get_class($this) . '===== Original Registration Data: ' . json_encode($recurringModel->getData()));

        // UPDATE & insert registration recurring
        if ($postData['STATUS'] == 'SUCCESS') {

            if($recurringModel->getStatus() != 'SUCCESS') {
                $recurringModel->setStatusType($postData['STATUSTYPE']);
                $recurringModel->setStatus($postData['STATUS']);
                $recurringModel->setCardNumber($postData['CARDNUMBER']);
            } else {
                $recurringModel->setUpdatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
            }

            try {
                $this->recurringRepository->save($recurringModel);
                $this->generateRecurringPayments($recurringModel);
            } catch (\Exception $e) {
                $this->logger->info(get_class($this) . '===== RECURRING REGISTRATION NOTIFY :: FAILED ON SAVE:' . $e->getMessage());
                echo "FAILED";
                return;
            }
        } else {
            $this->logger->info('===== Notify Recurring Registration Controller ===== Status '.@$postData['STATUS']);
            $this->logger->info(get_class($this) . '===== RECURRING REGISTRATION NOTIFY :: Status ' . @$postData['STATUS']);
        }

        $this->logger->info(get_class($this) . '===== RECURRING REGISTRATION NOTIFY :: END ');

        return;
    }


    public function generateRecurringPayments($registration) {

        $this->logger->info(get_class($this) . '===== RECURRING REGISTRATION NOTIFY :: Generating Scheduled Payments!');
        $startDate = new \DateTime($registration->getStartDate());
        $endDate = new \DateTime($registration->getEndDate());

        $occurence = (int)$registration->getExecuteDate();
        $startDate->add(new \DateInterval("P1D"));
        $endDate->add(new \DateInterval("P1D"));

        $dateRange = new \DatePeriod($startDate, new \DateInterval("P1D"), $endDate);

        $amount = $this->order->getGrandTotal();
        $firstOrder = $this->recurringpaymentFactory->create();
        $firstOrderData = [
            'customer_id' => $registration->getCustomerId(),
            'bill_number' => $registration->getBillNumber(),
            'merchant_transid' => $registration->getBillNumber(),
            'amount' => $amount,
            'card_number' => $registration->getCardNumber(),
            'recurred_at' => $this->timezoneInterface->date()->format('Y-m-d H:i:s'),
            'scheduled_at' => $this->timezoneInterface->date()->format('Y-m-d'),
            'recurring_status' => 1, //0 for pending
            'payment_datetime' => $this->timezoneInterface->date()->format('Y-m-d H:i:s'),
            'order_id' => $this->order->getEntityId(),
            'currency' => '360' //IDR
        ];
        $firstOrder->setData($firstOrderData);
        $this->recurringpaymentRepository->save($firstOrder);

        $i = 1;
        foreach($dateRange as $date) {
            if((int)$date->format("d") == $occurence) {
                $i++;

                $model = $this->recurringpaymentFactory->create();
                $billNumber = $registration->getBillNumber();
                $recurData = [
                    'customer_id' => $registration->getCustomerId(),
                    'bill_number' => $billNumber,
                    'merchant_transid' => $billNumber . "-" . $i,
                    'recurring_status' => 0,
                    'card_number' => $registration->getCardNumber(),
                    'amount' => $amount,
                    'scheduled_at' => $date->format('Y-m-d')
                ];
                $model->setData($recurData);

                try {
                    $this->recurringpaymentRepository->save($model);
                } catch (\Exception $e) {
                    //$this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the registration.'));
                    $this->logger->info(get_class($this) . $e->getMessage());
                }

//                $log = $date->format('Y-m-d')." recurring created \n";
//                $this->logger->info(get_class($this) . $log);
            }
        }

    }




    public function recurringUpdate($postData) {
        $connection = $this->resourceConnection->getConnection();

        $this->logger->info('===== Notify Recurring Update Controller ===== Start');
        $dokuRecurOrder = $connection->fetchRow("SELECT * FROM doku_transaction where recurring_billnumber = '" . $postData['BILLNUMBER'] . "'");

        $requestParams = json_decode($dokuRecurOrder['request_params'], true);
        $mallId = isset($requestParams['MALLID'])?$requestParams['MALLID']:$requestParams['req_mall_id'];
        $sharedKey = $requestParams['SHAREDID'];

        //MALLID+CHAINMERCHANT+BILLNUMBER+CUSTOMERID+STATUS +<shared key>.
        $words = sha1($mallId . $postData['CHAINMERCHANT']
            . $postData['BILLNUMBER'] .$postData['CUSTOMERID']
            . $postData['STATUS'] . $sharedKey);

        if ($postData['WORDS'] != $words) {
            $this->logger->info('===== Notify Recurring Update Controller ===== Words not match!');
            return;
        }
        $this->logger->info('===== Notify Recurring Update Controller ===== Words match!');

        if ($postData['STATUS'] == 'SUCCESS') {
            $sql = sprintf("
                Update doku_recurring_registration set status_type=%s, updated_at=%s where customer_id=%s",
                $postData['STATUSTYPE'],
                $this->timezoneInterface->date()->format('Y-m-d H:i:s'),
                $postData['CUSTOMERID']
            );
            $connection->query($sql);
            $this->logger->info('===== Notify Recurring Update Controller ===== Updated Done!');
        } else {
            $this->logger->info('===== Notify Recurring Update Controller ===== Status '.@$postData['STATUS']);
        }
        $this->logger->info('===== Notify Recurring Update Controller ===== End');
    }


    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     * Bypass form key validator since params from DOKU does not contain form key --leogent
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

}
