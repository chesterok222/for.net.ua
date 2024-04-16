<?php

namespace Ecomitize\Review\Model\ResourceModel\Review;

class Collection extends \Magento\Review\Model\ResourceModel\Review\Collection
{
    protected function _initSelect()
    {
        \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection::_initSelect();
        $this->getSelect()->join(
            ['detail' => $this->getReviewDetailTable()],
            'main_table.review_id = detail.review_id',
            ['detail_id', 'store_id', 'title', 'detail', 'nickname', 'phone', 'email', 'customer_id']
        );
        return $this;
    }

}
