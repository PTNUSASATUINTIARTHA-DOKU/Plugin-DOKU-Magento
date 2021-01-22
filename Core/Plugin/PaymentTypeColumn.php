<?php
/**
 *
 * User: leogent <leogent@gmail.com>
 * Date: 5/16/19
 * Time: 2:32 PM
 */

namespace Doku\Core\Plugin;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\Registry;


class PaymentTypeColumn
{
    private $messageManager;
    private $collection;
    private $registry;

    public function __construct(MessageManager $messageManager,
                                SalesOrderGridCollection $collection,
                                Registry $registry
    )
    {

        $this->messageManager = $messageManager;
        $this->collection = $collection;
        $this->registry = $registry;
    }

    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    )
    {
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_grid_data_source') {
            if ($result instanceof $this->collection
            ) {
                if (is_null($this->registry->registry('doku_transaction'))) {
                    $select = $this->collection->getSelect();
                    $select->joinLeft(

                        ["dokutrans" => $this->collection->getTable("doku_transaction")],
                        'main_table.entity_id = dokutrans.order_id',
                        array('payment_type')
                    );

                    $this->registry->register('doku_transaction', true);
                }
            }
            return $this->collection;
        }
        return $result;
    }
}
