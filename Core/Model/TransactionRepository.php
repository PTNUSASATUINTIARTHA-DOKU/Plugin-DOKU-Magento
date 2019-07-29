<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 3:18 AM
 */

namespace Doku\Core\Model;

use Doku\Core\Api\TransactionRepositoryInterface;
use Doku\Core\Api\Data;
use Doku\Core\Model\ResourceModel\Transaction as ResourceTransaction;
use Doku\Core\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\CouldNotDeleteException;

class TransactionRepository implements TransactionRepositoryInterface
{

    public function __construct(
        ResourceTransaction $resource,
        TransactionFactory $transactionFactory,
        \Doku\Core\Api\Data\TransactionInterfaceFactory $dataTransactionFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        Data\TransactionSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    )
    {
        $this->resource = $resource;
        $this->transactionFactory = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(Data\TransactionInterface $transaction) {
        try {
            $this->resource->save($transaction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    public function getById($transactionId)
    {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $transactionId);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('The Recurring Registration with the "%1" ID doesn\'t exist.', $transactionId));
        }
        return $transaction;
    }

    public function getByTransIdMerchant($transIdMerchant) {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $transIdMerchant, Transaction::TRANS_ID_MERCHANT);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('DOKU Transaction with the "%1" TRANSIDMERCHANT doesn\'t exist.', $transIdMerchant));
        }
        return $transaction;
    }

    public function getByOrderId($orderId) {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $orderId, Transaction::ORDER_ID);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('DOKU Transaction with the "%1" order_id doesn\'t exist.', $orderId));
        }
        return $transaction;
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Doku\Core\Model\ResourceModel\Recurring\Collection $collection */
        $collection = $this->transactionCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\RecurringSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function delete(Data\TransactionInterface $transaction)
    {
        try {
            $this->resource->delete($transaction);
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