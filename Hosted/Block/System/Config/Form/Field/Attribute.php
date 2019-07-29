<?php

namespace Doku\Hosted\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Attribute extends \Magento\Config\Block\System\Config\Form\Field
{    
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setReadonly('readonly');
        $element->setValue("{base_url}/dokuhosted/payment/redirect");
        return $element->getElementHtml();

    }
}