<?php

namespace Ecomitize\Review\Block\Adminhtml;

class Grid extends \Magento\Review\Block\Adminhtml\Grid
{

    protected function _prepareColumns()
    {

        $this->addColumn(
            'phone',
            [
                'header' => __('Phone'),
                'filter_index' => 'rdt.phone',
                'index' => 'phone',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'filter_index' => 'rdt.email',
                'index' => 'email',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        return parent::_prepareColumns();
    }
}
