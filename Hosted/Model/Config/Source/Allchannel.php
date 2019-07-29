<?php

namespace Doku\Hosted\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Allchannel implements ArrayInterface {

    public function toOptionArray() {

        $result = [];
        foreach($this->toArray() as $value => $label) {
            $result[] = array(
                'label' => $label,
                'value' => $value
            );
        }
        return $result;
    }

    public static function toArray() {
        return [
            '28' => 'ib_permata_hosted',
            '29' => 'bca_va_hosted',
            '41' => 'mandiri_va_hosted',
            '22' => 'sinarmas_va_hosted',
            '32' => 'cimb_va_hosted',
            '33' => 'ib_danamon_hosted',
            '34' => 'bri_va_hosted',
            '35' => 'alfa_hosted',
            '31' => 'Indomaret_hosted',
            '02' => 'mandiri_clickpay_hosted',
            '06' => 'epay_bri_hosted',
            '25' => 'ib_muamalat_hosted',
            '26' => 'danamon_va_hosted',
            '36' => 'permata_va_hosted',
            '19' => 'cimb_click_hosted',
            '37' => 'kredivo_hosted'
        ];
    }

}
