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

class Recurring extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = [])
    {
        $this->collectionFactory = $collectionFactory;
        $this->customerSession = $customerSession;

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
}