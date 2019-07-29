<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 3:18 AM
 */

namespace Doku\Core\Model;

use Doku\Core\Api\RecurringRepositoryInterface;
use Doku\Core\Api\Data;
use Doku\Core\Model\ResourceModel\Recurring as ResourceRecurring;
use Doku\Core\Model\ResourceModel\Recurring\CollectionFactory as RecurringCollectionFactory;
use \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class RecurringRepository implements RecurringRepositoryInterface
{

    public function __construct(
        ResourceRecurring $resource,
        RecurringFactory $recurringFactory,
        \Doku\Core\Api\Data\RecurringInterfaceFactory $dataRecurringFactory,
        RecurringCollectionFactory $recurringCollectionFactory,
        Data\RecurringSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    )
    {
        $this->resource = $resource;
        $this->recurringFactory = $recurringFactory;
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(Data\RecurringInterface $recurring) {
        try {
            $this->resource->save($recurring);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    public function getById($recurringId)
    {
        $recurring = $this->recurringFactory->create();
        $this->resource->load($recurring, $recurringId);
        if (!$recurring->getId()) {
            throw new NoSuchEntityException(__('The Recurring Registration with the "%1" ID doesn\'t exist.', $recurringId));
        }
        return $recurring;
    }

    public function getByBillNumber($billNumber) {
        $recurring = $this->recurringFactory->create();
        $this->resource->load($recurring, $billNumber, 'bill_number');
        if (!$recurring->getId()) {
            throw new NoSuchEntityException(__('The Recurring Registration with the "%1" ID doesn\'t exist.', $billNumber));
        }
        return $recurring;
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Doku\Core\Model\ResourceModel\Recurring\Collection $collection */
        $collection = $this->recurringCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\RecurringSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function delete(Data\RecurringInterface $recurring)
    {
        try {
            $this->resource->delete($recurring);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($recurringId)
    {
        return $this->delete($this->getById($recurringId));
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