<?php
/**
 * Doku
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://dokucommerce.com/Doku-Commerce-License.txt
 *
 *
 * @package    Doku_RefundRequest
 * @author     Extension Team
 *
 *
 */
namespace Doku\RefundRequest\Model\Attribute\Source;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Doku\RefundRequest\Model\ResourceModel\Status as dokuRefundStatus;

class RefundType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Remind Status values
     */
    const REFUND_PARTIAL = 'refund_partial';
    const REFUND_FULL = 'refund_full';
    const RETURN_STOCK = 'return_stock';
    const NA = null;

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {

        return [
            ['value' => self::REFUND_PARTIAL,  'label' => __('Refund Partial')],
            ['value' => self::REFUND_FULL,  'label' => __('Refund Full')],
            ['value' => self::RETURN_STOCK,  'label' => __('Return Items')],
            ['value' => self::NA,  'label' => __('N/A')]
        ];
    }

    public static function toArray() {
        return [
            self::REFUND_PARTIAL => 'Refund Partial',
            self::REFUND_FULL => 'Refund Full',
            self::RETURN_STOCK => 'Return Items',
            self::NA => 'N/A'
        ];
    }
}
