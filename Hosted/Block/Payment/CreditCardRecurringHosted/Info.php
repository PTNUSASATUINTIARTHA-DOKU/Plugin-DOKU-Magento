<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/17/19
 * Time: 1:38 AM
 */

namespace Doku\Hosted\Block\Payment\CreditCardRecurringHosted;

use Doku\Core\Api\RecurringRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use Doku\Core\Api\TransactionRepositoryInterface;
use Doku\Core\Api\RecurringpaymentRepositoryInterface;

class Info extends \Magento\Payment\Block\Info
{
    protected $_template = 'Doku_Hosted::payment/info/creditcard-recurring-hosted.phtml';


    public function __construct(
            Context $context,
            TransactionRepositoryInterface $transactionRepository,
            RecurringRepositoryInterface $recurringRepository,
            RecurringpaymentRepositoryInterface $recurringpaymentRepository,
            \Magento\Framework\Registry $coreRegistry,
            array $data = [])
    {
        $this->transactionRepository = $transactionRepository;
        $this->recurringRepository = $recurringRepository;
        $this->recurringpaymentRepository = $recurringpaymentRepository;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    public function getRecurringPayment() {
        $order = $this->_coreRegistry->registry('current_order');

        if($order) {
            try {
                $recurPayment = $this->recurringpaymentRepository->getByOrderId($order->getId());
                return $recurPayment;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            }
        }


        return false;
    }

    public function getRegistrationUrl($recurPayment) {
        $registration = $this->recurringRepository->getByBillNumber($recurPayment->getBillNumber());
        return $this->getUrl('dokucore/recurring/edit', ['id' => $registration->getId()]);
    }


}