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
            RecurringRepositoryInterface $recurringRepository
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
        return parent::__construct($context);
    }

    public function execute() {
        $path = "";
        $this->logger->info('===== Update Card Redirect Controller  ===== Start');
        $post = $this->getRequest()->getParams();
        $this->updateCardRedirect($post);
        return;
    }


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
