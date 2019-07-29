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

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Doku\RefundRequest\Model\ResourceModel\Status;

class RefundOrder implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $orderStatusCollection;

    /**
     * @var Status
     */
    protected $dokuRefundStatus;

    /**
     * RefundOrder constructor.
     * @param CollectionFactory $orderStatusCollection
     * @param Status $dokuRefundStatus
     */
    public function __construct(
        CollectionFactory $orderStatusCollection,
        Status $dokuRefundStatus
    ) {
        $this->orderStatusCollection = $orderStatusCollection;
        $this->dokuRefundStatus = $dokuRefundStatus;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    public function getStatus()
    {
        $status = $this->orderStatusCollection->create();
        return $status;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $array = [];
        foreach ($this->getStatus() as $value) {
            $array[] = ['value' => $value->getStatus(), 'label' => $value->getLabel()];
        }
        return $array;
    }
}
