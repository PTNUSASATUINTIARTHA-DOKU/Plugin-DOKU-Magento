<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 2:36 AM
 */

namespace Doku\Core\Api;


interface TransactionRepositoryInterface
{

    public function save(Data\TransactionInterface $transaction);

    public function getById($transactionId);

    public function getByTransIdMerchant($transIdMerchant);

    public function getByOrderId($orderId);

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    public function delete(Data\TransactionInterface $transaction);

    public function deleteById($id);

}