<?php

namespace Ledyer\Payment\Block\Checkout;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Ledyer\Payment\Helper\ConfigHelper;

class ToggleButtons extends Template
{
    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * @param Template\Context $context
     * @param ConfigHelper $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ConfigHelper $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Get b2c button text
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getB2CText()
    {
        return $this->config->getB2CText();
    }

    /**
     * Get b2b button text
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getB2BText()
    {
        return $this->config->getB2BText();
    }

    /**
     * Get b2c button url
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getB2CUrl()
    {
        return $this->config->getB2CUrl();
    }

    /**
     * Get b2b button url
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getB2BUrl()
    {
        return $this->config->getB2BUrl();
    }

    /**
     * Get color code
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getColor()
    {
        return $this->config->getButtonColor();
    }

    /**
     * Check if payment method is enabled
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function isEnabled()
    {
        return $this->config->getIsActive();
    }
}
