<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */
namespace Ledyer\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;

class ConfigHelper extends AbstractHelper
{
    public const PATH_CLIENT_ID = 'payment/ledyer/client_id';
    public const PATH_CLIENT_SECRET = 'payment/ledyer/client_secret';
    public const PATH_SANDBOX_ENABLED = 'payment/ledyer/sandbox_mode';
    public const PATH_SANDBOX_ENV = 'payment/ledyer/sandbox_env';
    public const PATH_IS_ACTIVE = 'payment/ledyer/active';
    public const PATH_ORDER_STATUS = 'payment/ledyer/order_status';
    public const PATH_TITLE = 'payment/ledyer/title';
    public const PATH_ALLOW_SPECIFIC = 'payment/ledyer/allowspecific';
    public const PATH_SPECIFIC_COUNTRY = 'payment/ledyer/specificcountry';
    public const PATH_MIN_TOTAL = 'payment/ledyer/min_order_total';
    public const PATH_SORT_ORDER = 'payment/ledyer/sort_order';
    public const PATH_BUTTON_COLOR = 'payment/ledyer/button_color';
    public const PATH_TERMS_URL = 'payment/ledyer/terms_url';
    public const PATH_PRIVACY_URL = 'payment/ledyer/privacy_url';
    public const PATH_DEBUG_MODE = 'payment/ledyer/debug';
    public const PATH_SECURITY_LEVEL = 'payment/ledyer/security_level';
    public const PATH_SHOW_NAME_FIELDS = 'payment/ledyer/show_name_fields';
    public const PATH_ALLOW_SHIPPING_ADDRESS = 'payment/ledyer/allow_shipping_address';
    public const PATH_STORE_ID = 'payment/ledyer/store_id';
    public const PATH_B2C_TEXT = 'payment/ledyer/b2c_text';
    public const PATH_B2B_TEXT = 'payment/ledyer/b2b_text';
    public const PATH_B2C_URL = 'payment/ledyer/b2c_url';
    public const PATH_TAX_CALCULATION_METHOD = 'tax/calculation/algorithm';
    public const LEDYER_METHOD_CODE = 'ledyer';
    public const IFRAME_CONTAINER_ID = 'ledyer-payment-method';
    public const LEDYER_CHECKOUT_URL_PATH = '/checkout/ledyer/';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Get config value by field
     *
     * @param string $field
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getConfigValue($field)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get client Id
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getClientId()
    {
        return $this->getConfigValue(self::PATH_CLIENT_ID);
    }

    /**
     * Get client secret
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function getClientSecret()
    {
        return $this->getConfigValue(self::PATH_CLIENT_SECRET);
    }

    /**
     * Get sandbox mode status
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getSandboxEnabled()
    {
        return $this->getConfigValue(self::PATH_SANDBOX_ENABLED);
    }

    /**
     * Get payement method enabled
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getIsActive()
    {
        if ($this->getTaxCalculationMethod() !== Calculation::CALC_ROW_BASE) {
            return '0';
        }

        return $this->getConfigValue(self::PATH_IS_ACTIVE);
    }

    /**
     * Get initial order status
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getOrderStatus()
    {
        return $this->getConfigValue(self::PATH_ORDER_STATUS);
    }

    /**
     * Get title
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getTitle()
    {
        return $this->getConfigValue(self::PATH_TITLE);
    }

    /**
     * Get allow specific value
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getAllowSpecific()
    {
        return $this->getConfigValue(self::PATH_ALLOW_SPECIFIC);
    }

    /**
     * Get allowed specific countries
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getSpecificCountries()
    {
        return $this->getConfigValue(self::PATH_SPECIFIC_COUNTRY);
    }

    /**
     * Get minimum total amount
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getMinTotal()
    {
        return $this->getConfigValue(self::PATH_MIN_TOTAL);
    }

    /**
     * Get sort order
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getSortOrder()
    {
        return $this->getConfigValue(self::PATH_SORT_ORDER);
    }

    /**
     * Get BUY button color
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getButtonColor()
    {
        return $this->getConfigValue(self::PATH_BUTTON_COLOR);
    }

    /**
     * Get Terms and Conditions URL
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getTermsUrl()
    {
        return $this->getConfigValue(self::PATH_TERMS_URL);
    }

    /**
     * Get Privacy Policy URL
     *
     * @return ?string
     * @throws NoSuchEntityException
     */
    public function getPrivacyUrl()
    {
        return $this->getConfigValue(self::PATH_PRIVACY_URL);
    }

    /**
     * Get checkout mode
     *
     * @return string
     */
    public function getMode()
    {
        if ($this->getSandboxEnabled()) {
            return $this->getConfigValue(self::PATH_SANDBOX_ENV);
        }
        return 'live';
    }

    /** 
     * Get the bootstrap url
     * 
     * @return string
     */
    public function getBootstrapUrl() 
    {
        if($this->getMode() === 'local' || $this->getMode() === 'local-fe') {
            return 'http://localhost:1337/bootstrap.iife.js';
        }
        return sprintf('https://checkout.%s.ledyer.com/bootstrap.js', $this->getMode());
    }

    public function getBootstrapEnv() 
    {
        if($this->getMode() === 'local' || $this->getMode() === 'local-fe') {
            return 'localhost';
        } else if ($this->getMode() === 'live') {
            return 'production';
        }
        return $this->getMode();
    }

    /**
     * Get the API url
     *
     * @param UrlPrefix $prefix
     * @param Endpoint $endpoint
     * @return string
     */
    public function getApiUrl(string $prefix, string $endpoint) 
    {
        if($this->getMode() === 'local') {
            if ($prefix === 'auth') {
                return sprintf(
                    'http://host.docker.internal:9001/%s',
                    $endpoint,
                );
            } else {
                return sprintf(
                    'http://host.docker.internal:8000/%s',
                    $endpoint,
                );
            }
        } else if ($this->getMode() === 'local-fe') {
            return sprintf(
                'https://%s.%s.%s%s',
                $prefix,
                'dev',
                'ledyer.com/',
                $endpoint
            );
        } else {
            return sprintf(
                'https://%s.%s.%s%s',
                $prefix,
                $this->getMode(),
                'ledyer.com/',
                $endpoint
            );
        }
    }

    /**
     * Check if debug mode is enabled
     *
     * @return string|null
     */
    public function isDebugEnabled()
    {
        return $this->getConfigValue(self::PATH_DEBUG_MODE);
    }

    /**
     * Get ledyer checkout security level
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getSecurityLevel()
    {
        return (int)$this->getConfigValue(self::PATH_SECURITY_LEVEL);
    }

    /**
     * Check if separate shipping address is enabled for ledyer iFrame
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getAllowShippingAddress()
    {
        return (bool)$this->getConfigValue(self::PATH_ALLOW_SHIPPING_ADDRESS);
    }

    /**
     * Get ledyer store id
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->getConfigValue(self::PATH_STORE_ID);
    }

    /**
     * Get tax calculation method
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getTaxCalculationMethod()
    {
        return $this->getConfigValue(self::PATH_TAX_CALCULATION_METHOD);
    }

    /**
     * Get iframe container id
     *
     * @return string
     */
    public function getContainerId()
    {
        return self::IFRAME_CONTAINER_ID;
    }

    /**
     * Get B2C button text
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getB2CText()
    {
        return $this->getConfigValue(self::PATH_B2C_TEXT);
    }

    /**
     * Get B2B button text
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getB2BText()
    {
        return $this->getConfigValue(self::PATH_B2B_TEXT);
    }

    /**
     * Get B2C button url
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getB2CUrl()
    {
        return $this->getConfigValue(self::PATH_B2C_URL);
    }

    /**
     * Get B2B button url
     *
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getB2BUrl()
    {
        return self::LEDYER_CHECKOUT_URL_PATH;
    }
}
