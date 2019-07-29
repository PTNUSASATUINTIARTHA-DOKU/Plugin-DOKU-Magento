<?php

namespace Doku\Core\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Attribute extends \Magento\Config\Block\System\Config\Form\Field {

    protected function _getElementHtml(AbstractElement $element) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $baseUrl = $scopeConfig->getValue("web/secure/base_url", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $element->setReadonly('readonly');

        /*
        if ($element->getId() == 'doku_general_config_general_notify_url') {
            $element->setValue($baseUrl . "dokucore/service/notify");
        } else if ($element->getId() == 'doku_general_config_general_identify_url') {
            $element->setValue($baseUrl . "dokucore/service/identify");
        } else if ($element->getId() == 'doku_general_config_general_review_url') {
            $element->setValue($baseUrl . "dokucore/service/review");
        } else if ($element->getId() == 'doku_general_config_general_redirect_url') {
            $element->setValue($baseUrl . "dokucore/service/redirect");
        } else if ($element->getId() == 'doku_hosted_config_cc_recurring_hosted_url_registration_notify') {
            $element->setValue($baseUrl . "dokucore/service/notify");
        } else if ($element->getId() == 'doku_hosted_config_cc_recurring_hosted_url_registration_redirect') {
            $element->setValue($baseUrl . "dokucore/service/redirect");
        } else if($element->getId() == 'doku_hosted_config_cc_recurring_hosted_url_recurring_update_notify') {
            $element->setValue($baseUrl . "dokucore/recurring/updatenotify");
        } else if($element->getId() == 'doku_hosted_config_cc_recurring_hosted_url_recurring_update_redirect') {
            $element->setValue($baseUrl . "dokucore/recurring/updateredirect");
        } else if($element->getId() == 'doku_hosted_config_cc_recurring_hosted_url_recurring_notify') {
            $element->setValue($baseUrl . "dokucore/recurring/recurnotify");
        } else if($element->getId() == 'doku_hosted_config_cc_recurring_hosted_url_recurring_getamount') {
            $element->setValue($baseUrl . "dokucore/recurring/recurgetamount");
        }
        */

        switch ($element->getId()) {
            case 'doku_general_config_general_notify_url':
                $element->setValue($baseUrl . "dokucore/service/notify");
                break;
            case 'doku_general_config_general_identify_url':
                $element->setValue($baseUrl . "dokucore/service/identify");
                break;
            case 'doku_general_config_general_review_url':
                $element->setValue($baseUrl . "dokucore/service/review");
                break;
            case 'doku_general_config_general_redirect_url':
                $element->setValue($baseUrl . "dokucore/service/redirect");
                break;
            case 'doku_hosted_config_cc_recurring_hosted_url_registration_notify':
            case 'payment_cc_recurring_hosted_url_registration_notify':
            case 'payment_us_cc_recurring_hosted_url_registration_notify':
            case 'payment_us_cc_recurring_hosted_url_registration_notify':
                $element->setValue($baseUrl . "dokucore/service/notify");
                break;
            case 'doku_hosted_config_cc_recurring_hosted_url_registration_redirect':
            case 'payment_cc_recurring_hosted_url_registration_redirect':
            case 'payment_us_cc_recurring_hosted_url_registration_redirect':
            case 'payment_id_cc_recurring_hosted_url_registration_redirect':
                $element->setValue($baseUrl . "dokucore/service/redirect");
                break;
            case 'doku_hosted_config_cc_recurring_hosted_url_recurring_update_notify':
            case 'payment_cc_recurring_hosted_url_recurring_update_notify':
            case 'payment_us_cc_recurring_hosted_url_recurring_update_notify':
            case 'payment_id_cc_recurring_hosted_url_recurring_update_notify':
                $element->setValue($baseUrl . "dokucore/recurring/updatenotify");
                break;
            case 'doku_hosted_config_cc_recurring_hosted_url_recurring_update_redirect':
            case 'payment_cc_recurring_hosted_url_recurring_update_redirect':
            case 'payment_us_cc_recurring_hosted_url_recurring_update_redirect':
            case 'payment_id_cc_recurring_hosted_url_recurring_update_redirect':
                $element->setValue($baseUrl . "dokucore/recurring/updateredirect");
                break;
            case 'doku_hosted_config_cc_recurring_hosted_url_recurring_notify':
            case 'payment_cc_recurring_hosted_url_recurring_notify':
            case 'payment_us_cc_recurring_hosted_url_recurring_notify':
            case 'payment_id_cc_recurring_hosted_url_recurring_notify':
                $element->setValue($baseUrl . "dokucore/recurring/recurnotify");
                break;
            case 'doku_hosted_config_cc_recurring_hosted_url_recurring_getamount':
            case 'payment_cc_recurring_hosted_url_recurring_getamount':
            case 'payment_us_cc_recurring_hosted_url_recurring_getamount':
            case 'payment_id_cc_recurring_hosted_url_recurring_getamount':
                $element->setValue($baseUrl . "dokucore/recurring/recurgetamount");
                break;


        }

        return $element->getElementHtml();
    }

}
