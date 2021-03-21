<?php

namespace Doku\Core\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;
//use Doku\Core\Helper\Data;

class GeneralConfiguration implements ConfigProviderInterface {
    protected $log;
    protected $timezoneInterface;

    CONST MALL_ID_PRODUCTION_CONFIG_PATH = 'doku_general_config/general/mall_id_production';
    CONST MALL_ID_DEVELOPMENT_CONFIG_PATH = 'doku_general_config/general/mall_id_development';
    CONST CHAIN_ID_PRODUCTION_CONFIG_PATH = 'doku_general_config/general/chain_id_production';
    CONST CAIN_ID_DEVELOPMENT_CONFIG_PATH = 'doku_general_config/general/chain_id_development';
    CONST SHARED_KEY_PRODUCTION_CONFIG_PATH = 'doku_general_config/general/shared_key_production';
    CONST SHARED_KEY_DEVELOPMENT_CONFIG_PATH = 'doku_general_config/general/shared_key_development';
    CONST EXPIRY_CONFIG_PATH = 'doku_general_config/general/expiry';
    CONST ENVIRONMENT_CONFIG_PATH = 'doku_general_config/general/environment';
    CONST SNEDER_EMAIL_CONFIG_PATH = 'doku_general_config/general/sender_mail';
    CONST SEMDER_NAME_CONFIG_PATH = 'doku_general_config/general/sender_name';  
    CONST BCC_EMAIL_CONFIG_PATH = 'doku_general_config/general/sender_mail_bcc';
    CONST MALL_ID_OFFUS_PRODUCTION_CONFIG_PATH = 'doku_general_config/installment/mall_id';
    CONST MALL_ID_OFFUS_DEVELOPMENT_CONFIG_PATH = 'doku_general_config/installment/mall_id_development';
    CONST SHARED_KEY_OFFUS_PRODUCTION_CONFIG_PATH = 'doku_general_config/installment/shared_key';
    CONST SHARED_KEY_OFFUS_DEVELOPMENT_CONFIG_PATH = 'doku_general_config/installment/shared_key_development';
    CONST INSTALLMENT_CONFIGURATION_CONFIG_PATH = 'doku_general_config/installment/installment_configuration';
    CONST ACTIVE_INSTALLMENT_CONFIG_PATH = 'doku_general_config/installment/active_installment';
    CONST ACTIVE_EDU_CONFIG_PATH = 'doku_general_config/edu/active_edu';
    CONST PAYMENTCHANELS_EDU_CONFIG_PATH = 'doku_general_config/edu/payment_channels_edu';
    CONST IP_WHITELIST_CONFIG_PATH = 'doku_general_config/whitelist/ip_whitelist';
    CONST ACTIVE_TOKENIZATION_CONFIG_PATH = 'doku_general_config/tokenization/active_tokenization'; 
    CONST KLIK_BCA_CORE_DESCRIPTION_CONFIG_PATH = 'payment/klik_bca_core/description';
    CONST BCA_KLIKPAY_CORE_DESCRIPTION_CONFIG_PATH = 'payment/bca_klikpay_core/description';
    CONST INSTALLMENT_AMOUNT_ABOVE = 'doku_general_config/installment/amount_above';
    CONST RECURRING_FLATSTATUS = 'payment/cc_recurring_hosted/flatstatus';
    CONST RECURRING_REGISTERAMOUNT_OPTION = 'payment/cc_recurring_hosted/registeramount_opt';
    CONST RECURRING_REGISTERAMOUNT = 'payment/cc_recurring_hosted/registeramount';
    CONST RECURRING_EXECUTEDATE = 'payment/cc_recurring_hosted/executedate';
    CONST URL_RECURRING_REG = 'payment/cc_recurring_hosted/url_recurring_reg';
    CONST URL_RECURRING_UPDATE = 'payment/cc_recurring_hosted/url_recurring_update';
    CONST URL_RECURRING_UNSUBSCRIBE = 'payment/cc_recurring_hosted/url_recurring_unsubscribe';
    CONST URL_RECURRING_STATUS = 'payment/cc_recurring_hosted/url_recurring_status';
    CONST URL_RECURRING_TRANS = 'payment/cc_recurring_hosted/url_recurring_trans';
    CONST URL_CAPTURE = 'payment/cc_authorization_hosted/url_capture';
    CONST URL_VOID = 'payment/cc_authorization_hosted/url_void';

    CONST REL_PAYMENT_CHANNEL = [
        'cc_hosted' => "15",
        'cc_authorization_hosted' => "15", //temp
        'cc_recurring_hosted' => "17",
        'alfa_hosted' => "35",
        'bca_va_hosted' => "29",
        'doku_wallet_hosted' => "04",
        'indomaret_hosted' => "31",
        'mandiri_clickpay_hosted' => "02",
        'ib_danamon_hosted' => "26",
        'ib_permata_hosted' => "28",
        'ib_muamalat_hosted' => "25",
        'epay_bri_hosted' => "06",
        'cimb_click_hosted' => "19",
        'permata_va_hosted' => "36",
        'danamon_va_hosted' => "33",
        'mandiri_va_hosted' => "41",
        'bri_va_hosted' => "34",
        'cimb_va_hosted' => "32",
        'kredivo_hosted' => "37",
        'sinarmas_va_hosted' => "22", 
        'bca_klikpay_core' => "18",
        'klik_bca_core' => "03",
        'doku_hosted_payment' => "0",
        'ovo_hosted' => '53',
        'linkaja_hosted' => '50',
        'bni_va_hosted' => "38",
        
        'permata_va_merchanthosted' => "36",
        'mandiri_va_merchanthosted' => "41",
        'sinarmas_va_merchanthosted' => "22",
        'danamon_va_merchanthosted' => "33",
        'bca_va_merchanthosted' => "29",
        'bri_va_merchanthosted' => "34",
        'cimb_va_merchanthosted' => "32",
        'alfa_merchanthosted' => "35",
        'indomaret_merchanthosted' => "31",
        'cc_merchanthosted' => "15",
        'doku_wallet_merchanthosted' => "04",
        'mandiri_clickpay_merchanthosted' => "02",
        'bni_va_merchanthosted' => "38"
    ];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
       \Psr\Log\LoggerInterface $logger,
        TimezoneInterface $timezoneInterface
    ){
        $this->log = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->timezoneInterface = $timezoneInterface;
    }

    public function getConfig() {
        $config = [
            'payment' => [
                'core' => [
                    'mall_id' => $this->getMallId(),
                    'environment' => $this->getEnvironment(),
                    'expiry' => $this->getExpiry(),
                    'request_url' => $this->getRequestUrl(),
                    'chain_id' => $this->getChainId(),
                    'installment_activation' => $this->getInstallmentActivation(),
                    'installment_tennor_configuration' => $this->getInstallmentTennorConfiguration(),
                    'installment_bank_active' => $this->getIsntallmentBankActive(),
                    'mip_request_url' => $this->getMIPRequestUrl(),
                    'installment_amount_above' => $this->getInstallmentAmountAbove()
                ],
                'bca_klikpay_core' => [
                    'description' => $this->getDescriptionBcaKlikPay(),
                    'admin_fee' => $this->getPaymentAdminFeeAmount("bca_klikpay_core"),
                    'admin_fee_type' => $this->getPaymentAdminFeeType("bca_klikpay_core"),
                    'disc_amount' => $this->getPaymentDiscountAmount("bca_klikpay_core"),
                    'disc_type' => $this->getPaymentDiscountType("bca_klikpay_core")
                ],
                'klik_bca_core' => [
                    'description' => $this->getDescriptionKlikBca(),
                    'admin_fee' => $this->getPaymentAdminFeeAmount("klik_bca_core"),
                    'admin_fee_type' => $this->getPaymentAdminFeeType("klik_bca_core"),
                    'disc_amount' => $this->getPaymentDiscountAmount("klik_bca_core"),
                    'disc_type' => $this->getPaymentDiscountType("klik_bca_core")
                ],
            ]
        ];
        
        return $config;
    }
    
    public function getEnvironment() {
        return $this->scopeConfig->getValue(SELF::ENVIRONMENT_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMallId() {
        if ($this->getEnvironment() == 'development') {
          return $this->scopeConfig->getValue(SELF::MALL_ID_DEVELOPMENT_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
          return $this->scopeConfig->getValue(SELF::MALL_ID_PRODUCTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getSharedKey() {
        if ($this->getEnvironment() == 'development') {
          return $this->scopeConfig->getValue(SELF::SHARED_KEY_DEVELOPMENT_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
          return $this->scopeConfig->getValue(SELF::SHARED_KEY_PRODUCTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }
    
   public function getChainId() {
        $chainId = '';
        if ($this->getEnvironment() == 'development') {
            $chainId = $this->scopeConfig->getValue(SELF::CAIN_ID_DEVELOPMENT_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return !empty($chainId) ? $chainId : 'NA';
        } else {
            $chainId = $this->scopeConfig->getValue(SELF::CHAIN_ID_PRODUCTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            return !empty($chainId) ? $chainId : 'NA';
        }
    }

    public function getSharedKeyOffUs() {
        if ($this->getEnvironment() == 'development') {
            return $this->scopeConfig->getValue(SELF::SHARED_KEY_OFFUS_DEVELOPMENT_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            return $this->scopeConfig->getValue(SELF::SHARED_KEY_OFFUS_PRODUCTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }

    public function getMallIdOffUs() {
        if ($this->getEnvironment() == 'development') {
           return $this->scopeConfig->getValue(SELF::MALL_ID_OFFUS_DEVELOPMENT_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
          return$this->scopeConfig->getValue(SELF::MALL_ID_OFFUS_PRODUCTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }
    
    public function getInstallmentConfig() {
        $config = $this->scopeConfig->getValue(SELF::INSTALLMENT_CONFIGURATION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $serializeConfig = @unserialize($config);
        if (!$serializeConfig) {
            $serializeConfig = @json_decode($config, true);
        }
        return $serializeConfig;
    }
    
    public function getInstallmentActivation() {
           return $this->scopeConfig->getValue(SELF::ACTIVE_INSTALLMENT_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRequestUrl() {
        if ($this->getEnvironment() == 'development') {
          return \Doku\Core\Helper\Data::REQUEST_URL_HOSTED_DEVELOPMENT;
        } else {
          return \Doku\Core\Helper\Data::REQUEST_URL_HOSTED_PRODUCTION;
        }
    }
    
    public function getExpiry() {
        return $this->scopeConfig->getValue(SELF::EXPIRY_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getSenderMail() {
        return $this->scopeConfig->getValue(SELF::SNEDER_EMAIL_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getSenderName() {
        return $this->scopeConfig->getValue(SELF::SEMDER_NAME_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getRelationPaymentChannel($code){
         $codeSet = self::REL_PAYMENT_CHANNEL;
         return $codeSet[$code];
    }
    
    public function getLablePaymentChannel(){
         $codeSet = array_keys(self::REL_PAYMENT_CHANNEL);
         return $codeSet;
    }
    
    public function getInstallmentTennorConfiguration() {
        $tennorList = array();
        if (is_array($this->getInstallmentConfig())) {
            foreach ($this->getInstallmentConfig() as $config) {
                unset($config['promo_id'], $config['installment_acquierer_code'], $config['is_on_us']);
                $tennorList[] = $config;
            }
            return $tennorList;
        }
    }

    public function getIsntallmentBankActive() {
		$bankList = array();
        file_put_contents(BP."/var/log/install.log", gettype($this->getInstallmentConfig()));
        if (is_array($this->getInstallmentConfig())) {
            foreach ($this->getInstallmentConfig() as $config) {
                if (!in_array($config['customer_bank'], $bankList)) {
                    $bankList[] = $config['customer_bank'];
                }
            }
            return $bankList;
        }
    }

    public function getSlectedInstallmentConfiguration($customer_bank, $tennor) {
        if (is_array($this->getInstallmentConfig())) {
            foreach ($this->getInstallmentConfig() as $config) {
                if ($config['customer_bank'] == $customer_bank && $config['tennor'] == $tennor) {
                    return $config;
                }
            }
        }
    }
    
    public function getActiveEdu() {
        return $this->scopeConfig->getValue(SELF::ACTIVE_EDU_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getPaymentChanelsEdu() {
        return $this->scopeConfig->getValue(SELF::PAYMENTCHANELS_EDU_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getIpWhitelist() {
        return $this->scopeConfig->getValue(SELF::IP_WHITELIST_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getActiveTokenization() {
        return $this->scopeConfig->getValue(SELF::ACTIVE_TOKENIZATION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getDescriptionBcaKlikPay() {
        $additionalDesc = $this->getLabelAdminFeeAndDiscount(
                $this->getPaymentAdminFeeAmount("bca_klikpay_core"), 
                $this->getPaymentAdminFeeType("bca_klikpay_core"), 
                $this->getPaymentDiscountAmount("bca_klikpay_core"), 
                $this->getPaymentDiscountType("bca_klikpay_core")
        );
        return $additionalDesc . $this->scopeConfig->getValue(SELF::KLIK_BCA_CORE_DESCRIPTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getDescriptionKlikBca() {
        $additionalDesc = $this->getLabelAdminFeeAndDiscount(
                $this->getPaymentAdminFeeAmount("klik_bca_core"), 
                $this->getPaymentAdminFeeType("klik_bca_core"), 
                $this->getPaymentDiscountAmount("klik_bca_core"), 
                $this->getPaymentDiscountType("klik_bca_core")
        );
        return $additionalDesc . $this->scopeConfig->getValue(SELF::BCA_KLIKPAY_CORE_DESCRIPTION_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMIPRequestUrl() {
        if ($this->getEnvironment() == 'development') {
          return \Doku\Core\Helper\Data::REQUEST_URL_MIP_DEVELOPMENT;
        } else {
          return \Doku\Core\Helper\Data::REQUEST_URL_MIP_PRODUCTION;
        }
    }
    
    public function getCheckStatusRequestUrl() {
        if ($this->getEnvironment() == 'development') {
          return \Doku\Core\Helper\Data::CHECKSTATUS_URL_DEVELOPMENT;
        } else {
          return \Doku\Core\Helper\Data::CHECKSTATUS_URL_PRODUCTION;
        }
    }
    
    public function getBccEmailAddress() {
        return $this->scopeConfig->getValue(SELF::BCC_EMAIL_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
   
    public function getPaymentAdminFeeAmount($paymentMethod) {
        return $this->scopeConfig->getValue('payment/' . $paymentMethod . '/admin_fee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentAdminFeeType($paymentMethod) {
        return $this->scopeConfig->getValue('payment/' . $paymentMethod . '/admin_fee_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentDiscountAmount($paymentMethod) {
        return $this->scopeConfig->getValue('payment/' . $paymentMethod . '/disc_amount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentDiscountType($paymentMethod) {
        return $this->scopeConfig->getValue('payment/' . $paymentMethod . '/disc_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCronConfiguration(){
        return $this->scopeConfig->getValue('doku_general_config/check_status/active_cron_checksatus', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function getJsMechantHosted(){
        if ($this->getEnvironment() == 'development') {
          return $this->scopeConfig->getValue('doku_general_config/js_merchant_hosted/development_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
          return $this->scopeConfig->getValue('doku_general_config/js_merchant_hosted/production_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
    }
    
    public function getLabelAdminFeeAndDiscount($adminFee, $adminFeeType, $discount, $discountType) {

        $lable = "";

        if (!empty($discount)) {
            if ($discountType == "percentage") {
                if ($discount < 100) {
                    $lable = "<b>Discount: </b>" . $discount . "%<br>";
                }
            } else {
                $lable = "<b>Discount: </b>Rp. " . number_format($discount, 2, ",", ".") . "<br>";
            }
        }
        
        if (!empty($adminFee)) {
            if ($adminFeeType == "percentage") {
                if ($adminFee < 100) {
                    $lable .= "<b>Admin Fee: </b>" . $adminFee . "%<br><br>";
                }
            } else {
                $lable .= "<b>Admin Fee:  </b>Rp." . number_format($adminFee, 2, ",", ".") . "<br><br>";
            }
        }

        return $lable;
    }
    
    public function getInstallmentAmountAbove(){
        return $this->scopeConfig->getValue(self::INSTALLMENT_AMOUNT_ABOVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }


    public function getRecurringFlatstatus() {
        $flatstatus = (int) $this->scopeConfig->getValue(self::RECURRING_FLATSTATUS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $flatstatus;
    }

    public function getRecurringRegisteramountOption() {
        $regAmountOption = (int) $this->scopeConfig->getValue(self::RECURRING_REGISTERAMOUNT_OPTION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $regAmountOption;
    }

    public function getRecurringRegisteramount() {
        if($this->getRecurringRegisteramountOption()) {
            $regAmount = (float)$this->scopeConfig->getValue(self::RECURRING_REGISTERAMOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $regAmount = 0;
        }
        return $regAmount;
    }
    public function getRecurringCharge() {
        return '10000.00';
    }
    public function getRecurringExecutedate() {
        return $this->scopeConfig->getValue(self::RECURRING_EXECUTEDATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getRecurringExecutemonth() {
        $month = $this->timezoneInterface->date()->format('m');
        $res = '';
        switch ($month) {
            case 1:
                $res = 'JAN';
                break;
            case 2:
                $res = 'FEB';
                break;
            case 3:
                $res = 'MAR';
                break;
            case 4:
                $res = 'APR';
                break;
            case 5:
                $res = 'MAY';
                break;
            case 6:
                $res = 'JUN';
                break;
            case 7:
                $res = 'JUL';
                break;
            case 8:
                $res = 'AUG';
                break;
            case 9:
                $res = 'SEP';
                break;
            case 10:
                $res = 'OCT';
                break;
            case 11:
                $res = 'NOV';
                break;
            case 12:
                $res = 'DEC';
                break;
        }
        return $res;
    }
    public function getURLRecurringReg() {
        return $this->scopeConfig->getValue(self::URL_RECURRING_REG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getURLRecurringUpdate() {
        return $this->scopeConfig->getValue(self::URL_RECURRING_UPDATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getURLRecurringUnsubsribe() {
        return $this->scopeConfig->getValue(self::URL_RECURRING_UNSUBSCRIBE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getURLRecurringStatus() {
        return $this->scopeConfig->getValue(self::URL_RECURRING_STATUS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getURLRecurringTrans() {
        return $this->scopeConfig->getValue(self::URL_RECURRING_TRANS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getURLCapture() {
        return $this->scopeConfig->getValue(self::URL_CAPTURE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getURLVoid() {
        return $this->scopeConfig->getValue(self::URL_VOID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
