<?php

namespace Ledyer\Payment\Logger;

use DateTimeZone;
use DateTime;
use Exception;
use Monolog\Logger as MonoLogger;
use Magento\Framework\Serialize\Serializer\Json;
use Ledyer\Payment\Helper\ConfigHelper;
use Ledyer\Payment\Api\MethodInterface;

class Logger extends MonoLogger
{
    /**
     * @var Json
     */
    public $json;

    /**
     * @var array
     */
    public $context = [];

    /**
     * @var ConfigHelper
     */
    public $config;

    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * @param string $name
     * @param Json $json
     * @param ConfigHelper $config
     * @param Cleaner $cleaner
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        string        $name,
        Json          $json,
        ConfigHelper  $config,
        Cleaner       $cleaner,
        array         $handlers = [],
        array         $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
        $this->json = $json;
        $this->config = $config;
        $this->cleaner = $cleaner;
    }

    /**
     * Log exception message
     *
     * @param Exception $e
     * @param array $context
     * @return void
     */
    public function logException(Exception $e, $context = [])
    {
        $input = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
        $logInput = $this->convertToString($input);

        return $this->forceLogging($logInput, $context);
    }

    /**
     * Logs api request if debug mode is enabled
     *
     * @param array $input
     * @param array $context
     * @return bool
     */
    public function logApiRequest(array $input, array $context = [])
    {
        if (!$this->config->isDebugEnabled()) {
            return false;
        }
        if ($this->config->getMode() === 'live') {
            $input = $this->cleaner->clean($input);
        }
        $logInput = $this->convertToString($input);

        $this->info('Api request');
        return $this->addRecord(self::DEBUG, $logInput, $context);
    }

    /**
     * Logs Api response if debug mode is enabled
     *
     * @param array $input
     * @param array $context
     * @return bool
     */
    public function logApiResponse(array $input, array $context = [])
    {
        if (!$this->config->isDebugEnabled()) {
            return false;
        }
        if ($this->config->getMode() === 'live') {
            $input = $this->cleaner->clean($input);
        }
        $logInput = $this->convertToString($input);
        $this->info('Api response');
        return $this->addRecord(self::DEBUG, $logInput, $context);
    }

    /**
     * Log notification if debug mode is enabled
     *
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function logNotification($message, $context)
    {
        if (!$this->config->isDebugEnabled()) {
            return false;
        }
        $logInput = $this->convertToString($context);

        return $this->addRecord(self::DEBUG, $message.$logInput);
    }

    /**
     * Converts array to string for logs
     *
     * @param array $input
     * @return string
     */
    public function convertToString(array $input)
    {
        $result = $this->json->serialize($input);

        return str_replace('\n', '', $result);
    }

    /**
     * Force log even if the debug mode is disabled
     *
     * @param string $message
     * @param array $context
     * @param int $level
     * @return void
     */
    public function forceLogging($message, array $context = [], $level = 400)
    {
        if (empty($context)) {
            $context = $this->context;
        }

        $input = $this->getHandlerInput($message, $level, $context);
        foreach ($this->handlers as $handler) {
            $handler->handle($input);
        }
    }

    /**
     * Prepares the input for the handlers
     *
     * @param string $message
     * @param int $level
     * @param array $context
     * @return array
     * @throws Exception
     */
    public function getHandlerInput($message, $level, $context)
    {
        $timezone = new DateTimeZone(date_default_timezone_get() ?: 'UTC');
        return [
            'message' => $message,
            'context' => $context,
            'level' => 400,
            'level_name' => 'ERROR',
            'extra'  => [],
            'datetime' => new DateTime('now', $timezone),
            'channel' => $this->name
        ];
    }
}
