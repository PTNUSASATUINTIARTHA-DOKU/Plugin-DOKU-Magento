<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 2:37 AM
 */

namespace Doku\Core\Api\Data;


interface RecurringInterface
{

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID      = 'id';
    const CUSTOMER_ID = 'customer_id';
    const STATUS = 'status';
    const STATUS_TYPE = 'status_type';
    const CARD_NUMBER = 'card_number';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const TOKEN_ID = 'token_id';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const EXECUTE_TYPE = 'execute_type';
    const EXECUTE_DATE = 'execute_date';
    const EXECUTE_MONTH = 'execute_month';
    const FLAT_STATUS = 'flat_status';
    const REGISTER_AMOUNT = 'register_amount';
    const SUBSCRIPTION_STATUS = 'subscription_status';
    const SUBSCRIPTION_UPDATED = 'subscription_updated';


    /**#@-*/
    public function getId();
    public function getCustomerId();
    public function getStatus();
    public function getStatusType();
    public function getCardNumber();
    public function getCreatedAt();
    public function getUpdatedAt();
    public function getTokenId();
    public function getStartDate();
    public function getEndDate();
    public function getExecuteType();
    public function getExecuteDate();
    public function getExecuteMonth();
    public function getFlatStatus();
    public function getRegisterAmount();
    public function getSubscriptionStatus();
    public function getSubscriptionUpdated();

    public function setId($id);
    public function setCustomerId($customerId);
    public function setStatus($status);
    public function setStatusType($statusType);
    public function setCardNumber($cardNumber);
    public function setCreatedAt($createdAt);
    public function setUpdatedAt($updatedAt);
    public function setTokenId($tokenId);
    public function setStartDate($startDate);
    public function setEndDate($endDate);
    public function setExecuteType($executeType);
    public function setExecuteDate($executeDate);
    public function setExecuteMonth($executeMonth);
    public function setFlatStatus($flatStatus);
    public function setRegisterAmount($registerAmount);
    public function setSubscriptionStatus($subscriptionStatus);
    public function setSubscriptionUpdated($subscriptionUpdated);

    public function unsubscribe();
    


}