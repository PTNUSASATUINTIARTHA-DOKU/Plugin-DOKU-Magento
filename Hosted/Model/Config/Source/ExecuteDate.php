<?php

namespace Doku\Hosted\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ExecuteDate implements ArrayInterface {

    public function toOptionArray() {
        $listing = [];
        for($i=1; $i<=31; $i++) {
            $listing[$i] = __("$i");
        }

        return $listing;
    }

}
