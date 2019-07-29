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

class SelectOption implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Remind Status values
     */
    const ENABLE = 0;
    const DISABLE = 1;

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ENABLE,  'label' => __('Enable')],
            ['value' => self::DISABLE,  'label' => __('Disable')]
        ];
    }
}
