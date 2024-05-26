<?php

namespace Ecomitize\ProductImport\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class ChildrenAs implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Products')], ['value' => 0, 'label' => __('Options')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [1 => __('Products'), 0 => __('Options')];
    }
}
