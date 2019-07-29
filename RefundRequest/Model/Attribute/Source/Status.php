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

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Remind Status values
     */
    const PENDING = 0;
    const ACCEPT = 1;
    const REJECT = 2;
    const REFUNDED = 3;
    const NA = null;

    /**
     * @var CollectionFactory
     */
    protected $orderStatusCollection;

    /**
     * @var dokuRefundStatus
     */
    protected $dokuRefundStatus;

    /**
     * Status constructor.
     * @param CollectionFactory $orderStatusCollection
     * @param dokuRefundStatus $dokuRefundStatus
     */
    public function __construct(
        CollectionFactory $orderStatusCollection,
        dokuRefundStatus $dokuRefundStatus
    ) {
        $this->orderStatusCollection = $orderStatusCollection;
        $this->dokuRefundStatus = $dokuRefundStatus;
    }

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->dokuRefundStatus->updateOrderRefundStatus();

        return [
            ['value' => self::PENDING,  'label' => __('Pending')],
            ['value' => self::ACCEPT,  'label' => __('Accept')],
            ['value' => self::REJECT,  'label' => __('Reject')],
            ['value' => self::REFUNDED,  'label' => __('Refunded')],
            ['value' => self::NA,  'label' => __('N/A')]
        ];
    }
}
