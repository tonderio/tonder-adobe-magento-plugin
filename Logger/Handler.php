<?php

namespace Tonder\Payment\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = '/var/log/tonder.log';
}
