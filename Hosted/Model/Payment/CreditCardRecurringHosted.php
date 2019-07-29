<?php

namespace Doku\Hosted\Model\Payment;

use Magento\Framework\App\ObjectManager;

class CreditCardRecurringHosted extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = 'cc_recurring_hosted';

    protected $_infoBlockType = \Doku\Hosted\Block\Payment\CreditCardRecurringHosted\Info::class;

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        /**
         * Do not display recurring option if not logged in
         */
//        $customerSession = ObjectManager::getInstance()->get('Magento\Customer\Model\Session');
//        if(!$customerSession->isLoggedIn()) {
//            return false;
//        }

        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        $this->_minOrderTotal = $this->getConfigData('minimum_subtotal');
        if($quote && $quote->getBaseGrandTotal() < $this->_minOrderTotal) {
            return false;
        }

        return true;
    }

}