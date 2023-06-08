<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Observer;

use Ledyer\Payment\Model\Api\LedyerSession;
use Ledyer\Payment\Helper\ConfigHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class UpdateLedyerSession implements ObserverInterface
{
    /**
     * @var LedyerSession
     */
    private $ledyerSession;

    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * @param LedyerSession $ledyerSession
     * @param ConfigHelper $config
     */
    public function __construct(
        LedyerSession $ledyerSession,
        ConfigHelper $config
    ) {
        $this->config = $config;
        $this->ledyerSession = $ledyerSession;
    }

    /**
     * Check if ledyer payment method is enabled and update ledyer session
     *
     * @param Observer $observer
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if ($this->config->getIsActive()) {
            $this->ledyerSession->updateSession();
        }
    }
}
