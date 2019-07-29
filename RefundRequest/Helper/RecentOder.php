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
namespace Doku\RefundRequest\Helper;

class RecentOder
{

    /**
     * @var Data
     */
    protected $helperConfigAdmin;

    /**
     * RecentOder constructor.
     * @param Data $helperConfigAdmin
     */
    public function __construct(
        Data $helperConfigAdmin
    ) {
        $this->helperConfigAdmin = $helperConfigAdmin;
    }

    //General config admin
    /**
     * @return string
     */
    public function getTemplate()
    {
        if ($this->helperConfigAdmin->getConfigEnableModule()) {
            $template =  'Doku_RefundRequest::order/recent.phtml';
        } else {
            $template = 'Magento_Sales::order/recent.phtml';
        }

        return $template;
    }

    /**
     * @return string
     */
    public function getTemplateMyOder()
    {
        if ($this->helperConfigAdmin->getConfigEnableModule()) {
            $template =  'Doku_RefundRequest::order/history.phtml';
        } else {
            $template = 'Magento_Sales::order/history.phtml';
        }
        return $template;
    }
}
