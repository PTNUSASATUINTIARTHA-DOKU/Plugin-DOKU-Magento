<?php
/**
 * Created by PhpStorm.
 * User: leogent <leogent@gmail.com>
 * Date: 2/3/19
 * Time: 11:51 PM
 */

namespace Doku\Core\Model\ResourceModel;


class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource
     *
     * @return void
     */

    public function _construct()
    {
        $this->_init('doku_transaction','id');
    }


}