<?php
namespace Doku\Core\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PaymentEdu implements ArrayInterface{

    public function toOptionArray()
    {

        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value)
        {

            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }

    public function toArray()
    {
        $catagoryList = array();
        $catagoryList['cc_merchanthosted'] = __('Credit Card (Merchant Hosted)');
        $catagoryList['cc_hosted'] = __('Credit Card (Doku Hosted)');
        $catagoryList['cc_recurring_hosted'] = __('Credit Card Recurring (Doku Hosted)');

        return $catagoryList;
    }

}
