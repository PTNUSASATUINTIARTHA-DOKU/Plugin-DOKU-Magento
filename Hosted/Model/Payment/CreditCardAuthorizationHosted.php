<?php

namespace Doku\Hosted\Model\Payment;


class CreditCardAuthorizationHosted extends \Magento\Payment\Model\Method\AbstractMethod
{

    const CODE = 'cc_authorization_hosted';
    protected $_code = 'cc_authorization_hosted';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    protected $_canRefundInvoicePartial = true;

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
        }
        die("BABI");
        return $this;
    }
}