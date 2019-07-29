<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/11/19
 * Time: 6:31 PM
 */

namespace Doku\Core\Block\Adminhtml\Recurring\Edit;


class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {

        parent::_construct();
        $this->setId('checkmodule_recurring_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Recurring Information'));
    }

}