<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 3:23 AM
 */

namespace Doku\Core\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface RecurringpaymentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get blocks list.
     *
     * @return \Doku\Core\Api\Data\TransactionInterface[]
     */
    public function getItems();

    /**
     * Set blocks list.
     *
     * @param \Doku\Core\Api\Data\TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

}