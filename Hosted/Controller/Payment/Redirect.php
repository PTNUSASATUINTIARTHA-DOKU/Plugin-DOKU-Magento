<?php

namespace Doku\Hosted\Controller\Payment;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Doku\Hosted\Model\DokuHostedConfigProvider;
use Doku\Core\Helper\Data;

class Redirect extends \Magento\Framework\App\Action\Action {

    protected $order;
    protected $logger;
    protected $session;
    protected $resourceConnection;
    protected $config;
    protected $helper;

    public function __construct(
            Order $order, 
            LoggerInterface $logger, 
            Session $session, 
            ResourceConnection $resourceConnection, 
            DokuHostedConfigProvider $config, 
            Data $helper, 
            \Magento\Framework\App\Action\Context $context
    ) {
        
        $this->order = $order;
        $this->logger = $logger;
        $this->session = $session;
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        $this->helper = $helper;

        return parent::__construct($context);
    }

    public function execute() {

        $path = "";

        $this->logger->info('===== Redirect Controller (Hosted) ===== Start');

        $post = $this->getRequest()->getPostValue();
        
        $postJson = json_encode($post, JSON_PRETTY_PRINT);

        $this->logger->info('post : ' . $postJson);

        $this->logger->info('===== Redirect Controller (Hosted) ===== Finding order...');

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('doku_transaction');

        $sql = "SELECT * FROM " . $tableName . " where trans_id_merchant = '" . $post['TRANSIDMERCHANT'] . "'";

        $dokuOrder = $connection->fetchRow($sql);

        if (!isset($dokuOrder['trans_id_merchant'])) {
            $this->logger->info('===== Notify Controller ===== Trans ID Merchant not found! in doku_transaction table');
            echo 'STOP';
            return;
        }
        
        $requestParams = json_decode($dokuOrder['request_params'], true);
        $mallId = $requestParams['MALLID'];
        $sharedKey = $requestParams['SHAREDID'];

        $order = $this->order->loadByIncrementId($post['TRANSIDMERCHANT']);

        if ($order->getEntityId()) {

            $this->logger->info('===== Redirect Controller (Hosted) ===== Order found!');


            $this->logger->info('===== Redirect Controller (Hosted) ===== Checking words');

            if (isset($post['PAYMENTCHANNEL']) && $post['PAYMENTCHANNEL'] == '17') {
                $words = $this->helper->doCreateWords(
                    array(
                        'amount' => $order->getGrandTotal(),
                        'sharedid' => $sharedKey,
                        'invoice' => $order->getIncrementId(),
                        'customerid' => $requestParams['CUSTOMERID'],
                        'billnumber' => $requestParams['BILLNUMBER']
                    )
                );
            } else {
                $words = $this->helper->doCreateWords(
                    array(
                        'amount' => $order->getGrandTotal(),
                        'invoice' => $order->getIncrementId(),
                        'currency' => '360',
                        'mallid' => $mallId,
                        'sharedid' => $sharedKey
                    )
                );
            }


            $this->logger->info('words : ' . $words);

            if ($words == $post['WORDS']) {
                $this->logger->info('===== Redirect Controller (Hosted) ===== Checking done');

                $this->logger->info('===== Redirect Controller (Hosted) ===== Check STATUSCODE');

                if ($post['STATUSCODE'] == '0000' || $post['STATUSCODE'] == '5511') {
                    $this->logger->info('===== Redirect Controller (Hosted) ===== STATUSCODE Success');
                    $path = "checkout/onepage/success";
                } else {
                    $path = "checkout/onepage/failure";
                    $order->cancel()->save();
                    $this->logger->info('===== Redirect Controller (Hosted) ===== STATUSCODE Failed!');
                }
            } else {
                $path = "";
                $this->messageManager->addError(__('Words not match!'));
                $this->logger->info('===== Notify Controller ===== Words not match!');
            }
        } else {
            $path = "";
            $this->messageManager->addError(__('Order not found'));
            $this->logger->info('===== Redirect Controller (Hosted) ===== Order not found');
        }
        
        $sql = "Update " . $tableName . " SET `created_at` = 'now()', `redirect_params` = '" . $postJson . "' where trans_id_merchant = '" . $post['TRANSIDMERCHANT'] . "'";
        $connection->query($sql);

        $this->logger->info('===== Redirect Controller (Hosted) ===== End');

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($path);
    }

}
