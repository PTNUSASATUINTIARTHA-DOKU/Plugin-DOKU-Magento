<?php
/**
 * Created by PhpStorm.
 * User: leogent <leogent@gmail.com>
 * Date: 2/3/19
 * Time: 11:48 PM
 */

namespace Doku\Core\Model;

//use Magento\Framework\Exception\RecurringException;

use Doku\Core\Api\Data\RecurringInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Recurring extends \Magento\Framework\Model\AbstractModel implements RecurringInterface
{

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct()
    {
        $this->_init('Doku\Core\Model\ResourceModel\Recurring');
    }

    /**#@-*/
    public function getId() {
        return $this->getData(self::ID);
    }

    public function getCustomerId(){
        return $this->getData(self::CUSTOMER_ID);
    }

    public function getStatus() {
        return $this->getData(self::STATUS);
    }

    public function getStatusType() {
        return $this->getData(self::STATUS_TYPE);
    }

    public function getCardNumber() {
        return $this->getData(self::CARD_NUMBER);
    }

    public function getCreatedAt() {
        return $this->getData(self::CREATED_AT);
    }

    public function getUpdatedAt() {
        return $this->getData(self::UPDATED_AT);
    }

    public function getTokenId() {
        return $this->getData(self::TOKEN_ID);
    }

    public function getStartDate() {
        return $this->getData(self::START_DATE);
    }

    public function getEndDate() {
        return $this->getData(self::END_DATE);
    }

    public function getExecuteType() {
        return $this->getData(self::EXECUTE_TYPE);
    }

    public function getExecuteDate() {
        return $this->getData(self::EXECUTE_DATE);
    }

    public function getExecuteMonth() {
        return $this->getData(self::EXECUTE_MONTH);
    }

    public function getFlatStatus() {
        return $this->getData(self::FLAT_STATUS);
    }

    public function getRegisterAmount() {
        return $this->getData(self::REGISTER_AMOUNT);
    }

    public function getSubscriptionStatus() {
        return $this->getData(self::SUBSCRIPTION_STATUS);
    }

    public function getSubscriptionUpdated() {
        return $this->getData(self::SUBSCRIPTION_UPDATED);
    }

    public function setId($id) {
        return $this->setData(self::ID, $id);
    }

    public function setCustomerId($customerId) {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    public function setStatus($status) {
        return $this->setData(self::STATUS, $status);
    }

    public function setStatusType($statusType) {
        return $this->setData(self::STATUS_TYPE, $statusType);
    }

    public function setCardNumber($cardNumber) {
        return $this->setData(self::CARD_NUMBER, $cardNumber);
    }

    public function setCreatedAt($createdAt) {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function setUpdatedAt($updatedAt) {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function setTokenId($tokenId) {
        return $this->setData(self::TOKEN_ID, $tokenId);
    }

    public function setStartDate($startDate) {
        return $this->setData(self::START_DATE, $startDate);
    }

    public function setEndDate($endDate) {
        return $this->setData(self::END_DATE, $endDate);
    }

    public function setExecuteType($executeType) {
        return $this->setData(self::EXECUTE_TYPE, $executeType);
    }

    public function setExecuteDate($executeDate) {
        return $this->setData(self::EXECUTE_DATE, $executeDate);
    }

    public function setExecuteMonth($executeMonth) {
        return $this->setData(self::EXECUTE_MONTH, $executeMonth);
    }

    public function setFlatStatus($flatStatus) {
        return $this->setData(self::FLAT_STATUS, $flatStatus);
    }

    public function setRegisterAmount($registerAmount) {
        return $this->setData(self::REGISTER_AMOUNT, $registerAmount);
    }

    public function setSubscriptionStatus($subscriptionStatus) {
        return $this->setData(self::SUBSCRIPTION_STATUS, $subscriptionStatus);
    }

    public function setSubscriptionUpdated($subscriptionUpdated) {
        return $this->setData(self::SUBSCRIPTION_UPDATED, $subscriptionUpdated);
    }

    public function unsubscribe() {
        return $this->setData(self::SUBSCRIPTION_STATUS, 0);
    }

}