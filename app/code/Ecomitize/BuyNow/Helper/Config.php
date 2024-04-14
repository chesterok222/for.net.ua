<?php

namespace Ecomitize\BuyNow\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Ecomitize\BuyNow\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     *
     */
    const BUY_NOW_EMAIL_TEMPLATE = 'buy_now/email/template';
    /**
     *
     */
    const BUY_NOW_EMAIL_SEND_TO = 'buy_now/email/send_to';
    /**
     *
     */
    const BUY_NOW_EMAIL_IDENTITY = 'buy_now/email/identity';

    /**
     * @return int
     */
    public function getEmailTemplate()
    {
        return $this->scopeConfig->getValue(self::BUY_NOW_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSendTo()
    {
        return $this->scopeConfig->getValue(self::BUY_NOW_EMAIL_SEND_TO, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->scopeConfig->getValue(self::BUY_NOW_EMAIL_IDENTITY, ScopeInterface::SCOPE_STORE);
    }

}
