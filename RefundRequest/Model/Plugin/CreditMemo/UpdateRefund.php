<?php
/**
 * Doku Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://dokucommerce.com/Doku-Commerce-License.txt
 *
 * @category   Doku
 * @package    Doku_RefundRequest
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 Doku Commerce Co. ( http://dokucommerce.com )
 *
 */

namespace  Doku\RefundRequest\Model\Plugin\CreditMemo;

class UpdateRefund extends \Magento\Sales\Model\Service\CreditmemoService
{


    /**
     * @param $subject
     * @param $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterRefund(\Magento\Sales\Model\Service\CreditmemoService $subject, $result)
    {
        $refundedTotal = $result->getGrandTotal();
        $orderId = $result->getOrderId();
        /** @TODO Update refund data */

        return $result;
    }


}
