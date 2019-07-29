<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 2:36 AM
 */

namespace Doku\Core\Api;


interface RecurringpaymentRepositoryInterface
{

    public function save(Data\RecurringpaymentInterface $payment);

    public function getById($paymentId);

    public function getByOrderId($orderId);

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    public function delete(Data\RecurringpaymentInterface $payment);

    public function deleteById($id);
}