<?php

namespace Doku\Hosted\Plugin\Magento\Checkout\Model\Session;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\DataObject;
use Doku\Hosted\Model\DokuHostedConfigProvider;

class SuccessValidator {

    protected $session;
    protected $order;
    protected $logger;
    protected $resourceConnection;
    protected $config;
    private $transportBuilder;
    private $dataObject;

    public function __construct(
            Session $session, 
            LoggerInterface $logger,
            Order $order, 
            ResourceConnection $resourceConnection,
            TransportBuilder $transportBuilder,
            DataObject $dataObject, 
            DokuHostedConfigProvider $config
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->order = $order;
        $this->resourceConnection = $resourceConnection;
        $this->transportBuilder = $transportBuilder;
        $this->dataObject = $dataObject;
        $this->config = $config;
    }

    public function afterIsValid(\Magento\Checkout\Model\Session\SuccessValidator $successValidator, $returnValue) {
        
        $dokuHostedCode = $this->config->getDokuHostedCode();
        
        $order = $this->order->loadByIncrementId($this->session->getLastRealOrder()->getIncrementId());

        if(in_array($order->getPayment()->getMethod(), $dokuHostedCode)){
            $order->setState(Order::STATE_PENDING_PAYMENT);
            $order->setStatus(Order::STATE_PENDING_PAYMENT);
            $this->session->getLastRealOrder()->setState(Order::STATE_PENDING_PAYMENT);
            $this->session->getLastRealOrder()->setStatus(Order::STATE_PENDING_PAYMENT);
            $order->save();
        }

        return $returnValue;
    }

}
