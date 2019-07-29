<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/15/19
 * Time: 2:20 PM
 */

namespace Doku\Core\Controller\Recurring;

use Magento\Sales\Model\Order;
use \Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Doku\Core\Helper\Data;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Request\Http;
use Doku\Core\Model\GeneralConfiguration;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Doku\Core\Api\RecurringRepositoryInterface;


class Updatecard  extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $session;
    protected $order;
    protected $logger;
    protected $resourceConnection;
    protected $config;
    protected $helper;
    protected $sessionFactory;
    protected $httpRequest;
    protected $storeManagerInterface;
    protected $_timezoneInterface;
    protected $customerSession;
    protected $recurringRepository;

    public function __construct(
        Session $session,
        Order $order,
        ResourceConnection $resourceConnection,
        Data $helper,
        Context $context,
        PageFactory $pageFactory,
        LoggerInterface $loggerInterface,
        SessionFactory $sessionFactory,
        Http $httpRequest,
        GeneralConfiguration $_generalConfiguration,
        StoreManagerInterface $_storeManagerInterface,
        TimezoneInterface $timezoneInterface,
        \Magento\Customer\Model\Session $customerSession,
        RecurringRepositoryInterface $recurringRepository
    ) {
        $this->session = $session;
        $this->logger = $loggerInterface;
        $this->order = $order;
        $this->resourceConnection = $resourceConnection;
        $this->config = $_generalConfiguration;
        $this->helper = $helper;
        $this->_pageFactory = $pageFactory;
        $this->sessionFactory = $sessionFactory;
        $this->httpRequest = $httpRequest;
        $this->storeManagerInterface = $_storeManagerInterface;
        $this->_timezoneInterface = $timezoneInterface;
        $this->customerSession = $customerSession;
        $this->recurringRepository = $recurringRepository;
        return parent::__construct($context);
    }

    public function execute() {

        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        $registrationId = $this->httpRequest->getParam('id');

        $recurringModel = $this->recurringRepository->getById($registrationId);

        if($recurringModel->getId()) {
            $registryObject->register('recurring_registration', $recurringModel);
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addError(__('Invalid Params!'));
            $this->_redirect($this->_redirect->getRefererUrl());
        }

    }

}