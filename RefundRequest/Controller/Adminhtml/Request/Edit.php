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

use Doku\RefundRequest\Model\RequestFactory;
use \Magento\Framework\Registry;


class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var bool|\Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry $_coreRegistry
     */
    protected $coreRegistry = null;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        RequestFactory $requestFactory,
        Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $registry;
        $this->requestFactory    = $requestFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $request = $this->requestFactory->create();
        $data = $this->getRequest()->getParams();
        $model          = $request->load($data['id']);

        $this->coreRegistry->register('doku_refundrequest', $model);
        $resultPage->getConfig()->getTitle()->prepend((__("Refund Request Dropdown Options")));
        return $resultPage;
    }

    /**
     * Check Rule
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
            ->isAllowed("Doku_RefundRequest::refundrequest_access_controller_label_edit");
    }
}
