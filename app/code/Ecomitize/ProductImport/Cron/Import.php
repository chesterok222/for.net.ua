<?php

namespace Ecomitize\ProductImport\Cron;

/**
 *
 */
class Import
{
    /**
     * @param \Ecomitize\ProductImport\Service\ProductImport $productImport
     * @param \Ecomitize\ProductImport\Helper\Config $config
     */
    public function __construct(
        \Ecomitize\ProductImport\Service\ProductImport $productImport,
        \Ecomitize\ProductImport\Helper\Config $config
    )
    {
        $this->productImport = $productImport;
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($this->config->getIsActive()) {
            $this->productImport->execute();
        }
    }
}
