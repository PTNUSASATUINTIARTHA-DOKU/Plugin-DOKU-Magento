<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/15/19
 * Time: 2:55 PM
 */

namespace Doku\Core\Block\Customer;

use \Magento\Framework\View\Element\Template\Context;
use Doku\Core\Model\ResourceModel\Recurring\CollectionFactory;
use Doku\Core\Api\RecurringpaymentRepositoryInterface;
use Doku\Core\Model\ResourceModel\Recurringpayment\CollectionFactory as RecurredCollectionFactory;
use Doku\Core\Model\GeneralConfiguration;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use \Psr\Log\LoggerInterface;


class Updatecard extends \Magento\Framework\View\Element\Template
{

    protected $timezoneInterface;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        RecurringpaymentRepositoryInterface $recurringpaymentRepository,
        RecurredCollectionFactory $recurredCollectionFactory,
        GeneralConfiguration $_generalConfiguration,
        TimezoneInterface $timezoneInterface,
        LoggerInterface $loggerInterface,
        array $data = [])
    {

        $this->_objectManager = $objectManager;
        $this->logger = $loggerInterface;
        $this->_timezoneInterface = $timezoneInterface;
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;
        $this->recurringpaymentRepository = $recurringpaymentRepository;
        $this->recurredCollectionFactory = $recurredCollectionFactory;
        $this->generalConfiguration = $_generalConfiguration;

        parent::__construct($context, $data);
    }

    public function getRecurring() {
        $scheduledCollection = $this->collectionFactory->create();
        $scheduledCollection->addFieldToFilter('status', 'SUCCESS');
        $scheduledCollection->addFieldToFilter('customer_id', $this->customerSession->getCustomer()->getId());
        $scheduledCollection->setOrder('start_date', 'DESC');
        return $scheduledCollection;
    }

    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::SHORT);
    }

    public function getUpdateCardUrl($recurring) {
        return $this->getUrl('dokucore/recurring/updatecard', ['id' => $recurring->getId()]);
    }

    public function getDokuUpdateCardUrl() {
//        return $this->getUrl('dokucore/recurring/test');
        return $this->generalConfiguration->getURLRecurringUpdate();
    }

    public function getFormData() {
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
        $registration = $registryObject->registry('recurring_registration');

        $scheduledCollection = $this->recurredCollectionFactory->create();
        $scheduledCollection->addFieldToFilter('recurring_status', 0);
        $scheduledCollection->addFieldToFilter('bill_number', $registration->getBillNumber());
        $scheduledCollection->setOrder('scheduled_at', 'ASC');

        $next = $scheduledCollection->getFirstItem();

        $mallId = $this->generalConfiguration->getMallId();
        $chainMerchant = $this->generalConfiguration->getChainId();
        $transidMerchant = $next->getMerchantTransid();
        $transidMerchant = $registration->getBillNumber();
        $requestDateTime =  $this->_timezoneInterface->date()->format('YmdHis');
        $sharedKey = $this->generalConfiguration->getSharedKey();

        $words = sha1($mallId . $chainMerchant . $registration->getBillNumber() . $registration->getCustomerId() . $sharedKey);

        $formData = [
            'MALLID' => $mallId,
            'CHAINMERCHANT' => $chainMerchant,
            'TRANSIDMERCHANT' => $transidMerchant,
            'WORDS' => $words,
            'REQUESTDATETIME' => $requestDateTime,
            'SESSIONID' => $transidMerchant,
            'CUSTOMERID' => $registration->getCustomerId(),
            'PAYMENTCHANNEL' => 17,
            'BILLNUMBER' => $registration->getBillNumber(),
            'REGISTERAMOUNT' => $this->generalConfiguration->getRecurringRegisteramount()
        ];

        $this->logger->info('===== Recur Update Card Controller :: Param: ' . json_encode($formData));

        return $formData;
    }
}