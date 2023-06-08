<?php

namespace Ledyer\Payment\Logger\Handlers;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class File extends Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * Name of the file where the logs will be logged
     *
     * @var string
     */
    protected $fileName = '/var/log/ledyer_api.log';
}
