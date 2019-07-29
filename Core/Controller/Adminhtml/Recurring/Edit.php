<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/11/19
 * Time: 6:23 PM
 */

namespace Doku\Core\Controller\Adminhtml\Recurring;


class Edit extends \Magento\Backend\App\Action
{

    public function execute()
    {

        // TODO: Implement execute() method.
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');

        $model = $this->_objectManager->create('Doku\Core\Model\Recurring');

        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        /**
         * Call DOKU delete subscription
         */


        $registryObject->register('core_recurring', $model);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

}