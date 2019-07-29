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
use Doku\Core\Model\ResourceModel\Recurringpayment\CollectionFactory;
use Doku\Core\Api\RecurringRepositoryInterface;
use Doku\Core\Api\RecurringpaymentRepositoryInterface;
use Doku\Core\Model\RecurringpaymentFactory;

class Recurnotify extends \Magento\Framework\App\Action\Action {

    protected $resourceConnection;
    protected $order;
    protected $generalConfiguration;
    protected $logger;
    protected $invoiceService;
    protected $builderInterface;
    protected $coreHelper;
    protected $timezoneInterface;
    protected $recurringRepository;
    protected $recurringpaymentRepository;
    protected $recurringpaymentFactory;
    protected $collectionFactory;

    public function __construct(
        LoggerInterface $loggerInterface,
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        Order $order,
        BuilderInterface $_builderInterface,
        InvoiceService $_invoiceService,
        GeneralConfiguration $_generalConfiguration,
        Data $_coreHelper,
        TimezoneInterface $timezoneInterface,
        CollectionFactory $collectionFactory,
        RecurringRepositoryInterface $recurringRepository,
        RecurringpaymentRepositoryInterface $recurringpaymentRepository,
        RecurringpaymentFactory $recurringpaymentFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
    ) {
        parent::__construct(
            $context
        );

        $this->resourceConnection = $resourceConnection;
        $this->builderInterface = $_builderInterface;
        $this->invoiceService = $_invoiceService;
        $this->logger = $loggerInterface;
        $this->generalConfiguration = $_generalConfiguration;
        $this->coreHelper = $_coreHelper;
        $this->timezoneInterface = $timezoneInterface;
        $this->recurringRepository = $recurringRepository;
        $this->recurringpaymentRepository = $recurringpaymentRepository;
        $this->recurringpaymentFactory = $recurringpaymentFactory;
        $this->collectionFactory = $collectionFactory;
        $this->objectManager = $objectManager;

        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->order = $order;
    }

    public function execute()
    {
        $this->logger->info('===== Recur Notify Controller ===== Start');

        try{

            $this->logger->info('===== Recur Notify Controller ===== Checking whitlist IP');

            if (!empty($this->generalConfiguration->getIpWhitelist())) {
                $ipWhitelist = explode(",", $this->generalConfiguration->getIpWhitelist());

                $clientIp = $this->coreHelper->getClientIp();

                if (!in_array($clientIp, $ipWhitelist)) {
                    $this->logger->info('===== Recur Notify Controller ===== IP not found');
                    return;
                }
            }

            $postjson = json_encode($_POST, JSON_PRETTY_PRINT);
            $this->logger->info('post : '. $postjson);
            $postData = $_POST;

            $mallId = $this->generalConfiguration->getMallId();
            $sharedKey = $this->generalConfiguration->getSharedKey();

            // TRANSIDMERCHANT + MALLID + <sharedkey> + CUSTOMERID + TOKENID + BILLNUMBER + RESULTMSG + VERIFYSTATUS
            $shaFormat = ($postData['TRANSIDMERCHANT'] . $mallId . $sharedKey
                . $postData['CUSTOMERID'] . $postData['TOKENID'] . $postData['BILLNUMBER']
                . $postData['RESULTMSG'] . $postData['VERIFYSTATUS']);
            $words = sha1($shaFormat);

            $this->logger->info('words raw : '. $shaFormat);
            $this->logger->info('words : '. $words);
            $this->logger->info('===== Recur Notify Controller ===== Checking words...');

            if ($postData['WORDS'] != $words) {
                $this->logger->info('===== Recur Notify Controller ===== Words not match!');
                return;
            }

            $this->logger->info('===== Recur Notify Controller ===== Checking done');
            $this->logger->info('===== Recur Notify Controller ===== Finding next scheduled payment...');

            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('doku_transaction');

            $billNumber = $postData['BILLNUMBER'];
            $recurringModel = $this->recurringRepository->getByBillNumber($billNumber);

            $scheduledCollection = $this->collectionFactory->create();
            $scheduledCollection->addFieldToFilter('recurring_status', 0);
            $scheduledCollection->addFieldToFilter('bill_number', $billNumber);
            $scheduledCollection->setOrder('scheduled_at', 'ASC');

            if($scheduledCollection->getSize() > 0) {
                $next = $scheduledCollection->getFirstItem();
            } else {
                throw new \Exception("No schedule found");
            }

            if($postData['RESULTMSG'] == 'SUCCESS' && $postData['RESPONSECODE'] == '0000') {
                $this->logger->info('===== Recur Notify Controller ===== RESULT SUCCESS...');
                //create order
                $registrationOrder = $this->order->loadByIncrementId($billNumber);

                $this->logger->info('===== Recur Notify Controller ===== CREATING ORDER...');
                $newOrder = $this->placeSubscriptionOrder($next, $registrationOrder, $recurringModel);

                //update scheduled
                if($newOrder) {
                    $this->logger->info('===== Recur Notify Controller ===== UPDATING SCHEDULED RECURRING...');
                    $next->setOrderId($newOrder->getEntityId());
                    $next->setTokenId($postData['TOKENID']);
                    $next->setCardNumber($postData['CARDNUMBER']);
                    $next->setCurrency($postData['CURRENCY']);
                    $next->setDokuPaymentId($postData['TRANSIDMERCHANT']);
                    $next->setResponseCode($postData['RESPONSECODE']);
                    $next->setApprovalCode($postData['APPROVALCODE']);
                    $next->setResultMessage($postData['RESULTMSG']);
                    $next->setBank($postData['BANK']);
                    $next->setVerifyId($postData['VERIFYID']);
                    $next->setVerifyScore($postData['VERIFYSCORE']);
                    $next->setVerifyStatus($postData['VERIFYSTATUS']);
                    $next->setSessionId($postData['SESSIONID']);
                    $paymentDateTime = \DateTime::createFromFormat("YmdHis", $postData['PAYMENTDATETIME']);
                    $next->setPaymentDatetime($paymentDateTime->format('Y-m-d H:i:s'));
                    $next->setRecurredAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
                    $next->setRecurringStatus(1);
                    $next->setWords($postData['WORDS']);
                    $this->recurringpaymentRepository->save($next);
                }
            } else {
                $this->logger->info('===== Recur Notify Controller ===== Trans ID Merchant not found! in doku_transaction table');
                throw new \Exception("Fail");
            }

            if(isset($postData['CUSTOMERID']) && !empty($postData['CUSTOMERID']) && isset($postData['TOKENID']) && !empty($postData['TOKENID'])){
                $tableName = $this->resourceConnection->getTableName('doku_recurring_registration');
                $sql = "SELECT * FROM " . $tableName . " where customer_id = '" . $postData['CUSTOMERID'] . "'";
                $dokuToken = $connection->fetchRow($sql);

                if(isset($dokuToken['customer_id'])){
                    $sql = "Update " . $tableName . " SET `token_id` = '".$postData['TOKENID']."' where customer_id = '" . $dokuToken['customer_id'] . "'";
                    $connection->query($sql);
                }
            }

            $this->logger->info('===== Recur Notify Controller ===== Updating success...');
            $this->logger->info('===== Recur Notify Controller ===== End');
            return;
        } catch(\Exception $e) {
            $this->logger->info('===== Recur Notify Controller ===== Generate code error : '. $e->getMessage());
            $this->logger->info('===== Recur Notify Controller ===== End');
            return;
        }
    }

    private function placeSubscriptionOrder($recurPayment, $orderModel, $registrationModel){

        $customer = $this->customerFactory->create();
        $customer->getResource()->load($customer, $orderModel->getCustomerId());
        if($customer->getEntityId()){
            $store = $this->_storeManager;
            $storeId = 1;
            $cartId = $this->cartManagementInterface->createEmptyCart();
            $quote = $this->cartRepositoryInterface->get($cartId);
            $quote->setStoreId($storeId);
            $quote->setCurrency();
            $customerRepo = $this->customerRepository->getById($customer->getEntityId());
            $quote->assignCustomer($customerRepo);

            foreach($orderModel->getAllItems() as $item) {
                $product = $this->_product->load($item->getProductId());
                $quote->addProduct($product, intval(1));
            }

            $shipMethod = $orderModel->getShippingMethod();

            $shippingAddress = $orderModel->getShippingAddress();

            $shipAddress = ['firstname' => $shippingAddress->getFirstname(),
                'lastname' => $shippingAddress->getLastname(),
                'street' => $shippingAddress->getStreet(),
                'city' => $shippingAddress->getCity(),
                'country_id' => $shippingAddress->getCountryId(),
                'region' => $shippingAddress->getRegion(),
                'region_id' => $shippingAddress->getRegionId(),
                'postcode' => $shippingAddress->getPostcode(),
                'telephone' => $shippingAddress->getTelephone(),
                'fax' => $shippingAddress->getFax(),
                'save_in_address_book' => 0];


            $billingAddress = $orderModel->getBillingAddress();

            $billAddress = ['firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'street' => $billingAddress->getStreet(),
                'city' => $billingAddress->getCity(),
                'country_id' => $billingAddress->getCountryId(),
                'region' => $billingAddress->getRegion(),
                'region_id' => $billingAddress->getRegionId(),
                'postcode' => $billingAddress->getPostcode(),
                'telephone' => $billingAddress->getTelephone(),
                'fax' => $billingAddress->getFax(),
                'save_in_address_book' => 0];

            // add address to quote
            $quote->getBillingAddress()->addData($billAddress);
            $quote->getShippingAddress()->addData($shipAddress);

            // set the shipping method
            $shippingAddressQuote = $quote->getShippingAddress();
            $shippingAddressQuote->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($shipMethod);


            $quote->setPaymentMethod('cc_recurring_hosted');
            $quote->setInventoryProcessed(false);
            $pMethod = ['method' => 'cc_recurring_hosted'];
            $quote->getPayment()->importData($pMethod);
            $quote->save();

            $quote->collectTotals();

            // create Order from Quote
            $quote = $this->cartRepositoryInterface->get($quote->getId());
            $orderId = $this->cartManagementInterface->placeOrder($quote->getId());
            $newOrder = $this->order->load($orderId);

            $emailSender = $this->objectManager->create('\Magento\Sales\Model\Order\Email\Sender\OrderSender');
            $emailSender->send($newOrder);
            $newOrder->setEmailSent(1);

            if ($newOrder->canInvoice() && !$newOrder->hasInvoices()) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $invoice = $this->invoiceService->prepareInvoice($newOrder);
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

                $payment = $newOrder->getPayment();
                $postData = $_POST;
                $payment->setLastTransactionId($postData['TRANSIDMERCHANT']);
                $payment->setTransactionId($postData['TRANSIDMERCHANT']);
                $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $_POST]);
                $message = __(json_encode($_POST, JSON_PRETTY_PRINT));
                $trans = $this->builderInterface;
                $transaction = $trans->setPayment($payment)
                    ->setOrder($newOrder)
                    ->setTransactionId($postData['TRANSIDMERCHANT'])
                    ->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $_POST])
                    ->setFailSafe(true)
                    ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
                $payment->addTransactionCommentsToOrder($transaction, $message);
                $payment->save();
                $transaction->save();

                if ($invoice && !$invoice->getEmailSent()) {
                    $invoiceSender = $objectManager->get('Magento\Sales\Model\Order\Email\Sender\InvoiceSender');
                    $invoiceSender->send($invoice);
                    $newOrder->addRelatedObject($invoice);
                    $newOrder->addStatusHistoryComment(__('Your Invoice for Order ID #%1.', $postData['TRANSIDMERCHANT']))
                        ->setIsCustomerNotified(true);
                }
            }

            $newOrder->setData('state', 'processing');
            $newOrder->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

            $newOrder->save();
            return $newOrder;
        }
    }
}
