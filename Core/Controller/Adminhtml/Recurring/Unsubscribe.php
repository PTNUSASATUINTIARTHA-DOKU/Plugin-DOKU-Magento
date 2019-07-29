<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/11/19
 * Time: 6:23 PM
 */

namespace Doku\Core\Controller\Adminhtml\Recurring;

use Doku\Core\Helper\Data;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Doku\Core\Model\GeneralConfiguration;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Doku\Core\Api\RecurringRepositoryInterface;
use Doku\Core\Api\RecurringpaymentRepositoryInterface;

class Unsubscribe extends \Magento\Backend\App\Action
{

    protected $_helper;
    protected $_timezoneInterface;
    protected $_scopeConfig;
    protected $_generalConfiguration;
    protected $recurringRepository;
    protected $recurringpaymentRepository;

    public function __construct(
        Context $context,
        Data $helper,
        TimezoneInterface $timezoneInterface,
        ScopeConfigInterface $scopeConfig,
        GeneralConfiguration $generalConfiguration,
        RecurringRepositoryInterface $recurringRepository,
        RecurringpaymentRepositoryInterface $recurringpaymentRepository
    )
    {
        parent::__construct($context);
        $this->_helper = $helper;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->_generalConfiguration = $generalConfiguration;
        $this->recurringRepository = $recurringRepository;
        $this->recurringpaymentRepository = $recurringpaymentRepository;
    }

    public function execute()
    {

        // TODO: Implement execute() method.
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');

        $model = $this->recurringRepository->getById($id);

        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $mallId = $this->_generalConfiguration->getMallId();
        $chainId = $this->_generalConfiguration->getChainId();
        $sharedKey = $this->_generalConfiguration->getSharedKey();

        $param = array(
            'MALLID' => $mallId,
            'CHAINMERCHANT' => $chainId,
            'IDENTIFIERTYPE' => 'B',
            'IDENTIFIERNO' => $model->getBillNumber(),
            'REQUESTTYPE' => 'C',
            'REQUESTDATETIME' => $this->_timezoneInterface->date()->format('YmdHis'),
        );

        $param['WORDS'] = sha1($mallId . $sharedKey . $param['IDENTIFIERTYPE'] . $param['IDENTIFIERNO'] . $param['REQUESTTYPE'] . $param['REQUESTDATETIME']);


        $url = $this->_generalConfiguration->getURLRecurringUnsubsribe();
        $unsub = $this->_helper->doDeleteSubscription($param);

        if($unsub) {
            if($unsub['RESULTMSG'] == 'SUCCESS') {
                $model->unsubscribe();
                $this->recurringRepository->save($model);
                $this->messageManager->addSuccessMessage(__('Recurring subscription successfully stopped'));
            } else {
                $this->messageManager->addErrorMessage(__('Fail to unsubscribe: ' . $unsub['MESSAGE'] . ' (' . $unsub['ERRORCODE']. ')'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Fail to unsubscribe'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

}