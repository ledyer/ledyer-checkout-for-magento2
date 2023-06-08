<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Controller\Session;

use Ledyer\Payment\Model\Api\LedyerSession;
use Ledyer\Payment\Helper\ConfigHelper;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Get implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var LedyerSession
     */
    private $ledyerSession;

    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * @param JsonFactory $jsonFactory
     * @param LedyerSession $ledyerSession
     * @param ConfigHelper $config
     */
    public function __construct(
        JsonFactory $jsonFactory,
        LedyerSession $ledyerSession,
        ConfigHelper $config
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->ledyerSession = $ledyerSession;
        $this->config = $config;
    }

    /**
     * Get ledyer session
     *
     * @return Json
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $jsonResult = $this->jsonFactory->create();
        if ($this->config->getIsActive()) {
            $result = $this->ledyerSession->getSession();
            $jsonResult->setData($result);
        }

        return $jsonResult;
    }
}
