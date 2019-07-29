<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 2:36 AM
 */

namespace Doku\Core\Api;


interface RecurringRepositoryInterface
{

    public function save(Data\RecurringInterface $recurring);

    public function getById($recurringId);

    public function getByBillNumber($billNumber);

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    public function delete(Data\RecurringInterface $recurring);

    public function deleteById($id);
}