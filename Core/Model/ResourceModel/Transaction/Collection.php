<?php
/**
 * Created by PhpStorm.
 * User: leogent <leogent@gmail.com>
 * Date: 2/3/19
 * Time: 11:53 PM
 */

namespace Doku\Core\Model\ResourceModel\Transaction;

/**
 * Recurring Collection
 * @package Doku\Core\Model\ResourceModel\Transaction
 * @author Leogent <leogent@gmail.com>
 */

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Initizalize resource collection
     *
     * @return void
     */

    public function _construct()
    {
        $this->_init('Doku\Core\Model\Transaction', 'Doku\Core\Model\ResourceModel\Transaction');
    }


}