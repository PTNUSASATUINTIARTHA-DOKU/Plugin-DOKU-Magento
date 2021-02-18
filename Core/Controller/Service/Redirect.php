<?php

namespace Doku\Core\Controller\Service;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Doku\Core\Helper\Data;
use Magento\Framework\Data\Form\FormKey\Validator;
use Doku\Core\Api\RecurringRepositoryInterface;
use Doku\Core\Api\TransactionRepositoryInterface;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface; 

class Redirect extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface {

    protected $order;
    protected $logger;
    protected $session;
    protected $resourceConnection;
    protected $helper;
    protected $timeZone;
    protected $formKeyValidator;
    protected $transactionRepository;
    protected $recurringRepository;
    protected $storeManagerInterface;
    protected $storeRepositoryInterface;

    public function __construct(
            Order $order, 
            LoggerInterface $logger, 
            Session $session, 
            ResourceConnection $resourceConnection, 
            Data $helper, 
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone,
            Validator $formKeyValidator,
            TransactionRepositoryInterface $transactionRepository,
            RecurringRepositoryInterface $recurringRepository,
            StoreManagerInterface $_storeManagerInterface,
            StoreRepositoryInterface $_storeRepositoryInterface
    ) {
        
        $this->order = $order;
        $this->logger = $logger;
        $this->session = $session;
        $this->resourceConnection = $resourceConnection;
        $this->helper = $helper;
        $this->timeZone = $timeZone;
        $this->formKeyValidator = $formKeyValidator;
        $this->recurringRepository = $recurringRepository;
        $this->transactionRepository = $transactionRepository;
        $this->storeManagerInterface = $_storeManagerInterface;
        $this->storeRepositoryInterface = $_storeRepositoryInterface;
        return parent::__construct($context);
    }

    public function execute() {
        $path = "";
        $this->logger->info('===== Redirect Controller  ===== Start');
        $post = $this->getRequest()->getParams();
        
        $postJson = json_encode($post, JSON_PRETTY_PRINT);

        $this->logger->info('REDIRECT PARAMS : ' . $postJson);

        $this->logger->info('===== Redirect Controller  ===== Finding order...');

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('doku_transaction');

        if(!isset($post['TRANSIDMERCHANT'])) {

            $path = "checkout/onepage/failure";
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath($path);
        }


        $sql = "SELECT * FROM " . $tableName . " where trans_id_merchant = '" . $post['TRANSIDMERCHANT'] . "'";

        $dokuOrder = $connection->fetchRow($sql);

        if (!isset($dokuOrder['trans_id_merchant'])) {
            $this->logger->info('===== Notify Controller ===== Trans ID Merchant not found! in doku_transaction table');
            
            $path = "";
            $this->messageManager->addError(__('Cannot found your order ID!'));
            
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath($path);
        }
        
        $requestParams = json_decode($dokuOrder['request_params'], true);
        $mallId = isset($requestParams['MALLID'])?$requestParams['MALLID']:$requestParams['req_mall_id'];
        $sharedKey = $requestParams['SHAREDID'];
        
        $requestAmount = 0;
        if(isset($requestParams['AMOUNT'])){
           $requestAmount = $requestParams['AMOUNT'];
        } else if(isset($requestParams['req_amount'])){
            $requestAmount = $requestParams['req_amount'];
        } else if(isset($requestParams['REQUEST']['req_amount'])){
            $requestAmount = $requestParams['REQUEST']['req_amount'];  
        }
        
        $expiryValue = 360;

        if (!empty($requestParams['EXPIRYTIME'])) {
            $expiryValue = $requestParams['EXPIRYTIME'];
        } else if (!empty($requestParams['req_expiry_time'])) {
            $expiryValue = $requestParams['req_expiry_time'];
        }

        $expiryGmtDate = date('Y-m-d H:i:s', (strtotime('+' . $expiryValue . ' minutes', time())));
        $expiryStoreDate = $this->timeZone->date(new \DateTime($expiryGmtDate))->format('Y-m-d H:i:s');

        $additionalParams = "";
        $vaNumber = "";
        if (isset($post['PAYMENTCODE']) && !empty($post['PAYMENTCODE'])) {
            $vaNumber = $post['PAYMENTCODE'];
            $additionalParams = " `va_number` = '" . $vaNumber . "', ";
        }

        $order = $this->order->loadByIncrementId($post['TRANSIDMERCHANT']);
        
        if ($order->getEntityId()) {
            
            $isSuccessOrder = false;

            $this->logger->info('===== Redirect Controller  ===== Order found!');


            $this->logger->info('===== Redirect Controller  ===== Checking words');

            // START recuring registration redirect
            if (isset($post['BILLNUMBER']) && !empty($post['BILLNUMBER'])) {
                $this->recurringRedirect($requestAmount, $sharedKey, $post, $order);
                return;
            }
            // END recuring registration redirect


            $wordsParams = array(
                'amount' => $requestAmount,
                'sharedid' => $sharedKey,
                'invoice' => $order->getIncrementId(),
                'statuscode' => $post['STATUSCODE']
            );            
            
            $this->logger->info('===== Redirect Controller  ===== words params: '.json_encode($wordsParams, JSON_PRETTY_PRINT));
            
            $words = $this->helper->doCreateWords($wordsParams);

            $storeCode = "";

            $this->logger->info('===== Redirect Controller  ===== words : ' . $words);

            if ($words == $post['WORDS']) {
                $this->logger->info('===== Redirect Controller  ===== Checking done');

                $this->logger->info('===== Checking Store Code =====');

                if (isset($post['SESSIONID'])) {
                    $storeCode = explode(':', $post['SESSIONID'])[0];
                    $this->logger->info('Store Code: ' . $storeCode);
                    $store = $this->storeRepositoryInterface->getActiveStoreByCode($storeCode);
                    $this->storeManagerInterface->setCurrentStore($store->getId());
                }

                $this->logger->info('===== Checking Store Code done =====');

                $this->logger->info('===== Redirect Controller  ===== Check STATUSCODE');

                if ($post['STATUSCODE'] == '0000' || $post['STATUSCODE'] == '5511') {
                    $isSuccessOrder = true;
                    $this->logger->info('===== Redirect Controller  ===== STATUSCODE Success');
                    $path = "checkout/onepage/success";
                } else {
                    $path = "checkout/onepage/failure";
                    $order->cancel()->save();
                    $this->logger->info('===== Redirect Controller  ===== STATUSCODE Failed!');
                }

                $this->logger->info('===== Redirect Controller ===== Send Email Order  ===== Start');

                $this->helper->sendDokuEmailOrder($order, $vaNumber, $dokuOrder, $isSuccessOrder, $expiryStoreDate);

                $this->logger->info('===== Redirect Controller ===== Send Email Order  ===== End');
                
            } else {
                $path = "";
                $order->cancel()->save();
                $this->messageManager->addError(__('Sorry, something went wrong!'));
                $this->logger->info('===== Redirect Controller ===== Words not match!');
            }
        } else {
            $path = "";
            $this->messageManager->addError(__('Order not found'));
            $this->logger->info('===== Redirect Controller  ===== Order not found');
        }

        $sql = "Update " . $tableName . " SET ".$additionalParams." `updated_at` = 'now()', `expired_at_gmt` = '".$expiryGmtDate."', `expired_at_storetimezone` = '".$expiryStoreDate."', `redirect_params` = '" . $postJson . "' where trans_id_merchant = '" . $post['TRANSIDMERCHANT'] . "'";
        $connection->query($sql);

        $this->logger->info('===== Redirect Controller  ===== End');

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($path);
    }

    public function recurringRedirect($requestAmount, $sharedKey, $post, $order)
    {
        if (!isset($post['WORDS'])) {
            $path = "checkout/onepage/failure";
            $this->messageManager->addError(__('Payment failed!'));
            return $this->_redirect($path);
        }


        $wordsParamsRec = array(
            'amount' => $requestAmount,
            'sharedid' => $sharedKey,
            'invoice' => $order->getIncrementId(),
            'customerid' => $post['CUSTOMERID'],
            'billnumber' => $post['BILLNUMBER']
        );
        $this->logger->info('===== Recurring Redirect Controller  ===== words params: ' . json_encode($wordsParamsRec, JSON_PRETTY_PRINT));
        $wordsRec = $this->helper->doCreateWords($wordsParamsRec);

        if ($wordsRec == $post['WORDS']) {
            $this->logger->info('===== Recurring Redirect Controller  ===== Checking done');
            $this->logger->info('===== Recurring Redirect Controller  ===== Success');
            $this->messageManager->addSuccess(__('Transaction Successfully. #' . $order->getIncrementId() . ' Billnumber #' . $post['BILLNUMBER']));
            $path = "checkout/onepage/success";
            return $this->_redirect($path);
        } else {
            $path = "checkout/onepage/failure";
            if ($order->getId()) {
                $order->cancel()->save();
            }

            $this->logger->info('===== Recurring Redirect Controller  ===== Failed!');
            $this->messageManager->addError(__('Sorry, something went wrong!'));
            return $this->_redirect($path);
        }

    }


    /**
     * Alternative function, in case redirect URL is using the same with payment redirect URL
     */
    public function updateCardRedirect($postData) {
        if($postData['STATUS'] == 'SUCCESS') {
            $this->messageManager->addSuccess(__('Credit Card information updated successfully'));
        } else {
            $this->messageManager->addError(__('Fail to update credit card'));
        }

        $path = "dokucore/recurring/index";
        return $this->_redirect($path);
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
