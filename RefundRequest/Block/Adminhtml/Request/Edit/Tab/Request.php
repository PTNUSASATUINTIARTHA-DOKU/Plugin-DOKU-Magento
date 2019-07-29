<?php
/**
 * Doku
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at thisURL:
 * http://dokucommerce.com/Doku-Commerce-License.txt
 *
 *
 * @package    Doku_RefundRequest
 * @author     Extension Team
 *
 *
 */

namespace Doku\RefundRequest\Block\Adminhtml\Request\Edit\Tab;

use \Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Filesystem;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Size Chart edit form main tab
 */
class Request extends Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Wysiwyg config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    protected $storeManagerInterface;

    protected $helper;

    /**
     * Country options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanOptions;

    /**
     * Label constructor.
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        Filesystem $filesystem,
        \Doku\RefundRequest\Helper\Data $helper,
        \Magento\Framework\Data\FormFactory $formFactory,
        StoreManagerInterface $_storeManagerInterface,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->booleanOptions = $booleanOptions;

        $this->filesystem           = $filesystem;
        $this->helper = $helper;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->storeManagerInterface = $_storeManagerInterface;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('post_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Refund Information'), 'class' => 'fieldset-wide']
        );


        $statusActions = array(
            0 => 'Pending',
            1 => 'Accept',
            2 => 'Reject'
        ) ;


        if ($model = $this->_coreRegistry->registry('doku_refundrequest')) {
            $form->addField('id', 'hidden', ['name' => 'id', 'value' => $model->getId()]);

            $fieldset->addField(
                'increment_id',
                'text',
                [
                    'label' => __('Order ID'),
                    'title' => __('Order ID'),
                    'name' => 'increment_id',
                    'required' => true,
                    'readonly' => true
                ]
            );


            if($model->getRefundStatus() == \Doku\RefundRequest\Model\Attribute\Source\Status::PENDING || $model->getRefundStatus() == \Doku\RefundRequest\Model\Attribute\Source\Status::NA) {
                $editable = true;
            } else {
                $editable = false;
            }

            $yesnoArray = [
                0 => 'No',
                1 => 'Yes'
            ];
            $fieldset->addField(
                'doku_refund_type',
                'select',
                [
                    'label' => __('Refund Type'),
                    'title' => __('Refund Type'),
                    'name' => 'doku_refund_type',
                    'options' => \Doku\RefundRequest\Model\Attribute\Source\RefundType::toArray(),
                    'disabled' => true
                ]
            );

            if($editable) {
                $fieldset->addField(
                    'refund_status',
                    'select',
                    [
                        'label' => __('Status'),
                        'title' => __('Status'),
                        'name' => 'refund_status',
                        'required' => false,
                        'options' => $statusActions
                    ]
                );

                $fieldset->addField(
                    'refund_status_remark',
                    'textarea',
                    [
                        'label' => __('Remark for Customer'),
                        'title' => __('Remark for Customer'),
                        'name' => 'refund_status_remark',
                        'required' => false,
                    ]
                );

                if($model->getDokuRefundType() == \Doku\RefundRequest\Model\Attribute\Source\RefundType::REFUND_PARTIAL) {
                    $fieldset->addField(
                        'refund_amount',
                        'text',
                        [
                            'label' => __('Refunded Amount'),
                            'title' => __('Refunded Amount'),
                            'name' => 'refund_amount',
                            'required' => true,
                            'class' => 'validate-numeric'
                        ]
                    );
                }
            } else {
                $fieldset->addField(
                    'refund_status',
                    'select',
                    [
                        'label' => __('Status'),
                        'title' => __('Status'),
                        'name' => 'refund_status',
                        'required' => false,
                        'options' => $statusActions,
                        'disabled' => true
                    ]
                );

                $fieldset->addField(
                    'refund_status_remark',
                    'textarea',
                    [
                        'label' => __('Remark for Customer'),
                        'title' => __('Remark for Customer'),
                        'name' => 'refund_status_remark',
                        'required' => false,
                        'disabled' => true
                    ]
                );

                if($model->getDokuRefundType() == \Doku\RefundRequest\Model\Attribute\Source\RefundType::REFUND_PARTIAL) {
                    $fieldset->addField(
                        'refund_amount',
                        'text',
                        [
                            'label' => __('Refunded Amount'),
                            'title' => __('Refunded Amount'),
                            'name' => 'refund_amount',
                            'required' => true,
                            'class' => 'validate-numeric'
                        ]
                    );
                }
            }


            $fieldset->addField(
                'reason_option',
                'text',
                [
                    'label' => __($this->helper->getDropdownTitle()),
                    'title' => __($this->helper->getDropdownTitle()),
                    'name' => 'reason_option',
                    'disabled' => true
                ]
            );

            $fieldset->addField(
                'reason_comment',
                'textarea',
                [
                    'label' => __('Detailed Reason'),
                    'title' => __('Detailed Reason'),
                    'name' => 'reason_comment',
                    'disabled' => true
                ]
            );

            $yesnoArray = [
                0 => 'No',
                1 => 'Yes'
            ];
            $fieldset->addField(
                'radio_option',
                'select',
                [
                    'label' => __($this->helper->getOptionTitle()),
                    'title' => __($this->helper->getOptionTitle()),
                    'name' => 'radio_option',
                    'options' => $yesnoArray,
                    'disabled' => true
                ]
            );



            if($model->getDokuAttachment()) {
                $attachment = $model->getDokuAttachment();

                $mediaUrl = $this->storeManagerInterface
                    ->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

                $image = $mediaUrl . 'doku_refund/'.$attachment;
                $fieldset->addField(
                    'doku_attachment',
                    'label',
                    [
                        'label' => __('Refund Status'),
                        'title' => __('Refund Status'),
                        'name' => 'doku_attachment',
                        'required' => true,
                        'value' => '',
                        'after_element_html' => "<img src='$image' />"
                    ]
                );

            }



            $form->addValues(
                $model->getData()
            );




        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Refund Information');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
