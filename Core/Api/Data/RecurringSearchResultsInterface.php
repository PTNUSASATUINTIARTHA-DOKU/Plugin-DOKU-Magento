<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 2/10/19
 * Time: 3:23 AM
 */

namespace Doku\Core\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface RecurringSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get blocks list.
     *
     * @return \Doku\Core\Api\Data\RecurringInterface[]
     */
    public function getItems();

    /**
     * Set blocks list.
     *
     * @param \Doku\Core\Api\Data\RecurringInterface[] $items
     * @return $this
     */
    public function setItems(array $items);

}