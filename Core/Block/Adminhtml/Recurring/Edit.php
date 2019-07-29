<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/11/19
 * Time: 6:30 PM
 */

namespace Doku\Core\Block\Adminhtml\Recurring;


class Edit extends \Magento\Backend\Block\Widget\Form\Container
{


    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Doku_Core';
        $this->_controller = 'adminhtml_recurring';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Item'));
        //$this->buttonList->update('delete', 'label', __('Delete Item'));

        $this->buttonList->remove('delete');
        $this->buttonList->add(
            'saveandcontinue',
            array(
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
                )
            ),
            -100
        );

        $this->buttonList->remove('save');
        $this->buttonList->remove('saveandcontinue');


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get('\Magento\Framework\Registry');

        $model = $registry->registry('core_recurring');

        if($model->getSubscriptionStatus() == 1) {


            $this->addButton(
                'unsubscribe',
                [
                    'label' => __('Unsubscribe'),
                    'class' => 'delete',
                    'onclick' => 'confirmSetLocation(\'' . __(
                            'Are you sure you want to unsubscribe this recurring payment?'
                        ) . '\', \'' . $this->getUnsubscribeUrl() . '\', {data: {}})'
                ]
            );
        }

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'hello_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'hello_content');
                }
            }
        ";
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('checkmodule_checkmodel')->getId()) {
            return __("Edit Item '%1'", $this->escapeHtml($this->_coreRegistry->registry('checkmodule_checkmodel')->getTitle()));
        } else {
            return __('New Item');
        }
    }

    /**
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        return $this->getUrl('*/*/unsubscribe', [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]);
    }
}