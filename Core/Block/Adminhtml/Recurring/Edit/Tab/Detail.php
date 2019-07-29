<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/11/19
 * Time: 6:32 PM
 */

namespace Doku\Core\Block\Adminhtml\Recurring\Edit\Tab;


class Detail extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('core_recurring');
        $isElementDisabled = false;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Detail')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

        $fieldset->addField(
            'customer_id',
            'text',
            array(
                'name' => 'customer_id',
                'label' => __('Customer ID'),
                'title' => __('Customer ID'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'card_number',
            'text',
            array(
                'name' => 'card_number',
                'label' => __('Card Number'),
                'title' => __('Card Number'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'created_at',
            'text',
            array(
                'name' => 'created_at',
                'label' => __('Created At'),
                'title' => __('Created At'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'updated_at',
            'text',
            array(
                'name' => 'updated_at',
                'label' => __('Updated At'),
                'title' => __('Updated At'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'token_id',
            'text',
            array(
                'name' => 'token_id',
                'label' => __('Token ID'),
                'title' => __('Token ID'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'bill_number',
            'text',
            array(
                'name' => 'bill_number',
                'label' => __('Bill Number'),
                'title' => __('Bill Number'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'bill_type',
            'text',
            array(
                'name' => 'bill_type',
                'label' => __('Bill Type'),
                'title' => __('Bill Type'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'start_date',
            'text',
            array(
                'name' => 'start_date',
                'label' => __('Start Date'),
                'title' => __('Start Date'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'end_date',
            'text',
            array(
                'name' => 'end_date',
                'label' => __('End Date'),
                'title' => __('End Date'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'execute_type',
            'text',
            array(
                'name' => 'execute_type',
                'label' => __('Execute Type'),
                'title' => __('Execute Type'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'updated_month',
            'text',
            array(
                'name' => 'execute_month',
                'label' => __('Execute Month'),
                'title' => __('Execute Month'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'flat_status',
            'text',
            array(
                'name' => 'flat_status',
                'label' => __('Flat Status'),
                'title' => __('Flat Status'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'register_amount',
            'text',
            array(
                'name' => 'register_amount',
                'label' => __('Register Amount'),
                'title' => __('Register Amount'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'subscription_status',
            'text',
            array(
                'name' => 'subscription_status',
                'label' => __('Subscription Status'),
                'title' => __('Subscription Status'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );

        $fieldset->addField(
            'subscription_updated',
            'text',
            array(
                'name' => 'subscription_updated',
                'label' => __('Subscription Updated At'),
                'title' => __('Subscription Updated At'),
                'disabled' => 'disabled'
                /*'required' => true,*/
            )
        );


        $fieldset->addField(
            'status',
            'text',
            array(
                'name' => 'status',
                'label' => __('status'),
                'title' => __('status'),
                'readonly' => 'readonly'
                /*'required' => true,*/
            )
        );
        /*{{CedAddFormField}}*/



        $formData = $model->getData();
        $formData['subscription_status'] = 1 ? 'Active' : 'Terminated';
        $form->setValues($formData);


        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Detail');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Detail');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
