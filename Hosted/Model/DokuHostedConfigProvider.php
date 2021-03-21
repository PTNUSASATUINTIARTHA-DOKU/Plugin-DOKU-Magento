<?php

namespace Doku\Hosted\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Doku\Core\Model\GeneralConfiguration;

class DokuHostedConfigProvider implements ConfigProviderInterface
{
    protected $_scopeConfig;
    protected $_generalConfiguration;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GeneralConfiguration $generalConfiguration
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->_generalConfiguration = $generalConfiguration;
    }

    
    public function getRelationPaymentChannel($code){
         return $this->_generalConfiguration->getRelationPaymentChannel($code);
    }
    
    public function getSharedKey(){
         return $this->_generalConfiguration->getSharedKey();
    }
    
    public function getPaymentDescription($paymentMethod){
        $additionalDesc = $this->_generalConfiguration->getLabelAdminFeeAndDiscount(
                $this->getPaymentAdminFeeAmount($paymentMethod), 
                $this->getPaymentAdminFeeType($paymentMethod), 
                $this->getPaymentDiscountAmount($paymentMethod), 
                $this->getPaymentDiscountType($paymentMethod)
        );
        return $additionalDesc . $this->_scopeConfig->getValue('payment/'.$paymentMethod.'/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getPaymentAdminFeeAmount($paymentMethod){
         return $this->_scopeConfig->getValue('payment/'.$paymentMethod.'/admin_fee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getPaymentAdminFeeType($paymentMethod){
         return $this->_scopeConfig->getValue('payment/'.$paymentMethod.'/admin_fee_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getPaymentDiscountAmount($paymentMethod){
         return $this->_scopeConfig->getValue('payment/'.$paymentMethod.'/disc_amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getPaymentDiscountType($paymentMethod){
         return $this->_scopeConfig->getValue('payment/'.$paymentMethod.'/disc_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDisplayAsDropdown($paymentMethod){
        return $this->_scopeConfig->getValue('payment/'.$paymentMethod.'/is_opt_dropdown', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getAllConfig(){
        return array('payment' => array_merge($this->_generalConfiguration->getConfig()['payment'], $this->getConfig()['payment']));
    }

    public function getConfig()
    {        
        $paymentList = \Doku\Core\Model\GeneralConfiguration::REL_PAYMENT_CHANNEL;
        
        $configData = array();
        
        foreach ($paymentList as $index => $value) {
            $expIdx = explode("_", $index);
            if (end($expIdx) == 'hosted' || $index == 'doku_hosted_payment') {
                $configData['payment'][$index]['description'] = $this->getPaymentDescription($index);
                $configData['payment'][$index]['admin_fee'] = $this->getPaymentAdminFeeAmount($index);
                $configData['payment'][$index]['admin_fee_type'] = $this->getPaymentAdminFeeType($index);
                $configData['payment'][$index]['disc_amount'] = $this->getPaymentDiscountAmount($index);
                $configData['payment'][$index]['disc_type'] = $this->getPaymentDiscountType($index);
                $configData['payment'][$index]['is_opt_dropdown'] = $this->getDisplayAsDropdown($index);
                $configData['payment'][$index]['transfer_dropdown_values'] = $this->getTransferDropdownValues();
                $configData['payment'][$index]['ib_dropdown_values'] = $this->getIbDropdownValues();
            }
        }

        return $configData;
    }

    public function getAllchannelDropdownValues() {
        $allowed = $this->_scopeConfig->getValue('payment/doku_hosted_payment/dropdown_values', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($allowed) {
            $allowed = explode(",", $allowed);
        } else {
            return [];
        }


        $all = \Doku\Hosted\Model\Config\Source\Allchannel::toArray();

        $intersect = [];
        foreach($allowed as $code) {
            if(array_key_exists($code, $all)) {
                $intersect[$code] = $all[$code];
            }
        }

        return $intersect;


        return $all;
    }

    public function getTransferDropdownValues() {
        $allowed = $this->_scopeConfig->getValue('payment/doku_hosted_payment/dropdown_values', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!$allowed) {
            return [];
        }

        $transferList = [
            '36' => 'permata_va_hosted',
            '29' => 'bca_va_hosted',
            '41' => 'mandiri_va_hosted',
            '22' => 'sinarmas_va_hosted',
            '32' => 'cimb_va_hosted',
            '26' => 'danamon_va_hosted',
            '34' => 'bri_va_hosted',
            '35' => 'alfa_hosted',
            '31' => 'indomaret_hosted',
            '38' => 'bni_va_hosted'
        ];

        $allowed = [];
        foreach($transferList as $id => $code) {
            $active = $this->_scopeConfig->getValue("payment/$code/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $label = $this->_scopeConfig->getValue("payment/$code/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($active) {
                $allowed[$id] = $label;
            }
        }

        return $allowed;
    }

    public function getIbDropdownValues() {
        $allowed = $this->_scopeConfig->getValue('payment/doku_hosted_payment/dropdown_values', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!$allowed) {
            return [];
        }

        $transferList = [
            '02' => 'mandiri_clickpay_hosted',
            '06' => 'epay_bri_hosted',
            '25' => 'ib_muamalat_hosted',
            '33' => 'ib_danamon_hosted',
            '28' => 'ib_permata_hosted',
            '19' => 'cimb_click_hosted',
            '37' => 'kredivo_hosted',
            '53' => 'ovo_hosted',
            '50' => 'linkaja_hosted',
        ];

        $allowed = [];
        foreach($transferList as $id => $code) {
            $active = $this->_scopeConfig->getValue("payment/$code/active", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $label = $this->_scopeConfig->getValue("payment/$code/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if($active) {
                $allowed[$id] = $label;
            }
        }

        return $allowed;
    }
}
