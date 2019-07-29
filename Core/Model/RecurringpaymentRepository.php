<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 3:18 AM
 */

namespace Doku\Core\Model;

use Doku\Core\Api\RecurringpaymentRepositoryInterface;
use Doku\Core\Api\Data;
use Doku\Core\Model\ResourceModel\Recurringpayment as ResourceRecurringpayment;
use Doku\Core\Model\ResourceModel\Recurringpayment\CollectionFactory as RecurringpaymentCollectionFactory;
use \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class RecurringpaymentRepository implements RecurringpaymentRepositoryInterface
{

    public function __construct(
        ResourceRecurringpayment $resource,
        RecurringpaymentFactory $recurringpaymentFactory,
        \Doku\Core\Api\Data\RecurringpaymentInterfaceFactory $dataRecurringpaymentFactory,
        RecurringpaymentCollectionFactory $recurringpaymentCollectionFactory,
        Data\RecurringpaymentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    )
    {
        $this->resource = $resource;
        $this->recurringpaymentFactory = $recurringpaymentFactory;
        $this->recurringpaymentCollectionFactory = $recurringpaymentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(Data\RecurringpaymentInterface $recurringpayment) {
        try {
            $this->resource->save($recurringpayment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    public function getById($id)
    {
        $recurringpayment = $this->recurringpaymentFactory->create();
        $this->resource->load($recurringpayment, $id);
        if (!$recurringpayment->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('The Recurring Payment with the "%1" ID doesn\'t exist.', $id));
        }
        return $recurringpayment;
    }

    public function getByOrderId($orderId) {
        $recurringpayment = $this->recurringpaymentFactory->create();
        $this->resource->load($recurringpayment, $orderId, 'order_id');
        if (!$recurringpayment->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('The Recurring Payment with the "%1" ORDER ID doesn\'t exist.', $orderId));
        }
        return $recurringpayment;
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Doku\Core\Model\ResourceModel\Recurring\Collection $collection */
        $collection = $this->recurringpaymentCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\RecurringSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function delete(Data\RecurringpaymentInterface $recurringpayment)
    {
        try {
            $this->resource->delete($recurringpayment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Framework\Api\SearchCriteria\CollectionProcessor'
            );
        }
        return $this->collectionProcessor;
    }
}