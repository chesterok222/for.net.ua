<?php

namespace Ecomitize\ProductImport\Model;

use Monolog\Logger;

/**
 * Class Handler
 * @package Ecomitize\ProductImport\Model
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::WARNING;

    /**
     * File name
     * @var string
     */
    protected $fileName = 'var/log/ecomitize-product-import.log';
}
