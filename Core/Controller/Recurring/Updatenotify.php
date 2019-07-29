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

class Updatenotify extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface {

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
        $this->logger->info('===== Update Card Notify Controller ===== Start');

        try{
            
            $this->logger->info('===== Update Card Notify Controller ===== Checking whitelist IP');
            
            if (!empty($this->generalConfiguration->getIpWhitelist())) {
                $ipWhitelist = explode(",", $this->generalConfiguration->getIpWhitelist());

                $clientIp = $this->coreHelper->getClientIp();

                if (!in_array($clientIp, $ipWhitelist)) {
                    $this->logger->info('===== Update Card Notify Controller ===== IP not found');
                    echo 'STOP';
                    return;
                }
            }

            $postjson = json_encode($_POST, JSON_PRETTY_PRINT);

            $this->logger->info('UPDATE CARD NOTIFY PARAMS : '. $postjson);

            $postData = $_POST;


            $customerid = $_POST['CUSTOMERID'];
            $billNumber = $_POST['BILLNUMBER'];
            $cardNumber = $_POST['CARDNUMBER'];
            $status = $_POST['STATUS'];
            $errorCode = $_POST['ERRORCODE'];
            $message = $_POST['MESSAGE'];
            $words = $_POST['WORDS'];
            $statusType = $_POST['STATUSTYPE'];


            $mallId = $this->generalConfiguration->getMallId();
            $sharedKey = $this->generalConfiguration->getSharedKey();
            $chainMerchant = $this->generalConfiguration->getChainId();

            $realWords = sha1($mallId . $chainMerchant . $billNumber . $customerid . $status . $sharedKey);
            $this->logger->info('words : '. $words);
            $this->logger->info('===== Update Card Notify Controller ===== Checking words...');

            if ($postData['WORDS'] != $words) {
                $this->logger->info('===== Notify Controller ===== Words not match!');
                throw new \Exception("Words not match");
            }

            $recurringModel = $this->recurringRepository->getByBillNumber($billNumber);
            if ($status == 'SUCCESS') {
                $recurringModel->setStatusType($postData['STATUSTYPE']);
                $recurringModel->setStatus($postData['STATUS']);
                $recurringModel->setCardNumber($postData['CARDNUMBER']);
                $recurringModel->setUpdatedAt($this->timezoneInterface->date()->format('Y-m-d H:i:s'));
                $this->recurringRepository->save($recurringModel);
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
