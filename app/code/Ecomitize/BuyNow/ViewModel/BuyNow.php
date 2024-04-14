<?php

namespace Ecomitize\BuyNow\ViewModel;


/**
 * Class BuyNow
 * @package Ecomitize\BuyNow\ViewModel
 */
class BuyNow implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected  $formKey;

    /**
     * @param \Magento\Framework\UrlInterface $_urlBuilder
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(\Magento\Framework\UrlInterface $_urlBuilder, \Magento\Framework\Data\Form\FormKey $formKey)
    {
        $this->_urlBuilder = $_urlBuilder;
        $this->formKey = $formKey;
    }

    /**
     * @param $product
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBuyNowParams($product)
    {
        $params = [
            'product' => $product->getId(),
            'productSku' => $product->getSku(),
            'productUrl' => $product->getProductUrl(),
            'url'   => $this->_getUrl('buy_now/place/order'),
            'form_key'   => $this->formKey->getFormKey()
        ];

        return json_encode($params);
    }

    /**
     * @param $route
     * @param $params
     * @return string
     */
    protected function _getUrl($route, $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
