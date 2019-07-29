<?php
/**
 * Doku
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://dokucommerce.com/Doku-Commerce-License.txt
 *
 *
 * @package    Doku_RefundRequest
 * @author     Extension Team
 *
 *
 */

namespace Doku\RefundRequest\Controller\Adminhtml\Request;

use Magento\Backend\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Doku\RefundRequest\Model\RequestFactory;

class Save extends Action
{
    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var LabelFactory
     */
    protected $labelFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Save constructor.
     * @param Session $backendSession
     * @param Registry $coreRegistry
     * @param LabelFactory $labelFactory
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Session $backendSession,
        Registry $coreRegistry,
        RequestFactory $requestFactory,
        Context $context,
        \Doku\RefundRequest\Helper\Email $emailSender,
        \Doku\RefundRequest\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        PageFactory $resultPageFactory
    )
    {
        $this->backendSession = $backendSession;
        $this->coreRegistry = $coreRegistry;
        $this->requestFactory = $requestFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->emailSender = $emailSender;
        $this->datetime = $datetime;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->localeLists = $localeLists;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $model = $this->requestFactory->create();

        $data = $this->getRequest()->getPostValue();
        $model->load($data['id']);
        $model->addData($data);
        if ($data) {
            try {

                $model->save();

                if ($model->getRefundStatus() == \Doku\RefundRequest\Model\Attribute\Source\Status::REJECT) {
                    $this->sendRejectEmail($model);
                } else if ($model->getRefundStatus() == \Doku\RefundRequest\Model\Attribute\Source\Status::ACCEPT) {
                    $this->sendAcceptEmail($model);
                }

                $this->messageManager->addSuccessMessage(__('The refund has been saved.'));
                $this->backendSession->setPostData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('*/*/');
                    return $resultRedirect;
                }
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving.'));
            }
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $resultRedirect->setPath('*/*/');
        return $resultRedirect;
    }

    /**
     * Check Rule
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed("Doku_RefundRequest::refundrequest_access_controller_request_save");
    }

    /**
     * @param $item
     */
    protected function sendAcceptEmail($item)
    {
        $customerEmail = $item->getCustomerEmail();
        $timezone = $this->scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\
        ScopeInterface::SCOPE_STORE);
        $date = $this->timezone->date();
        $timezoneLabel = $this->getTimezoneLabelByValue($timezone);
        $date = $date->format('Y-m-d h:i:s A') . " " . $timezoneLabel;
        $emailTemplate = $this->helper->getAcceptEmailTemplate();
        $emailTemplateData = [
            'incrementId' => $item["increment_id"],
            'id' => $item["id"],
            'timeApproved' => $date,
            'customerName' => $item["customer_name"]
        ];

        if($item->getRefundStatusRemark()) {
            $emailTemplateData['refundRemark'] = $item->getRefundStatusRemark();
        }

        $this->emailSender->sendEmail($customerEmail, $emailTemplate, $emailTemplateData);
    }

    /**
     * @param $item
     */
    protected function sendRejectEmail($item)
    {
        $customerEmail = $item->getCustomerEmail();
        $timezone = $this->scopeConfig->getValue('general/locale/timezone', \Magento\Store\Model\
        ScopeInterface::SCOPE_STORE);
        $date = $this->timezone->date();
        $timezoneLabel = $this->getTimezoneLabelByValue($timezone);
        $date = $date->format('Y-m-d h:i:s A')." ".$timezoneLabel;
        $emailTemplate = $this->helper->getRejectEmailTemplate();
        $emailTemplateData = [
            'incrementId' => $item["increment_id"],
            'id' => $item["id"],
            'timeApproved'=> $date,
            'customerName' => $item["customer_name"]
        ];

        if($item->getRefundStatusRemark()) {
            $emailTemplateData['refundRemark'] = $item->getRefundStatusRemark();
        }

        $this->emailSender->sendEmail($customerEmail, $emailTemplate, $emailTemplateData);
    }

    protected function getTimezoneLabelByValue($timezoneValue)
    {
        $timezones = $this->localeLists->getOptionTimezones();
        $label = '';
        foreach ($timezones as $zone) {
            if ($zone["value"] == $timezoneValue) {
                $label = $zone["label"];
            }
        }
        return $label;
    }
}
