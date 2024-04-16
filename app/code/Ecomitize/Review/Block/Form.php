<?php

namespace Ecomitize\Review\Block;

class Form extends \Magento\Review\Block\Form
{

    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('Ecomitize_Review::form.phtml');
    }
}
