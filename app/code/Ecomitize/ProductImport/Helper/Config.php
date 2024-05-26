<?php

namespace Ecomitize\ProductImport\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Ecomitize\ProductImport\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     *
     */
    const PRODUCT_IMPORT_GENERAL_ACTIVE = 'product_import/general/active';
    /**
     *
     */
    const PRODUCT_IMPORT_GENERAL_UA_URL = 'product_import/general/ua_url';
    /**
     *
     */
    const PRODUCT_IMPORT_GENERAL_RU_URL = 'product_import/general/ru_url';

    /**
     *
     */
    const PRODUCT_IMPORT_CHILDREN_MANAGE_STOCK = 'product_import/children/manage_stock';

    /**
     *
     */
    const PRODUCT_IMPORT_CHILDREN_CHILDREN_AS = 'product_import/children/children_as';

    /**
     * @return int
     */
    public function getIsActive()
    {
        return $this->scopeConfig->getValue(self::PRODUCT_IMPORT_GENERAL_ACTIVE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getUaUrl()
    {
        return $this->scopeConfig->getValue(self::PRODUCT_IMPORT_GENERAL_UA_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getRuUrl()
    {
        return $this->scopeConfig->getValue(self::PRODUCT_IMPORT_GENERAL_RU_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function getChildrenManageStock()
    {
        return (bool)$this->scopeConfig->getValue(self::PRODUCT_IMPORT_CHILDREN_MANAGE_STOCK, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function getChildrenAsProduct()
    {
        return (bool)$this->scopeConfig->getValue(self::PRODUCT_IMPORT_CHILDREN_CHILDREN_AS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $availability
     * @param $outOfStock
     * @return bool
     */
    public function getIsAvailable($availability, $outOfStock)
    {
        $childrenManageStock = $this->getChildrenManageStock();

        if (!$childrenManageStock) {
            return true;
        }

        return !in_array($availability, $outOfStock);
    }

}
