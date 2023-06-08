<?php

/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2023 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Plugin\Compatibility\Klarna;

use Ledyer\Payment\Helper\ConfigHelper;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Shipping;

class ShippingPlugin
{
    /**
     * @var Url
     */
    private Url $url;

    /**
     * @var RedirectInterface
     */
    private RedirectInterface $redirect;

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @param Url $url
     * @param RedirectInterface $redirect
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Url $url,
        RedirectInterface $redirect,
        ConfigHelper $configHelper
    ) {
        $this->url = $url;
        $this->redirect = $redirect;
        $this->configHelper = $configHelper;
    }

    /**
     * Remove klarna shipping gateway from Ledyer checkout shipping options
     *
     * @param Shipping $subject
     * @param callable $proceed
     * @param string $carrierCode
     * @param RateRequest $request
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function aroundCollectCarrierRates(Shipping $subject, callable $proceed, $carrierCode, $request)
    {
        if ($this->configHelper->getIsActive()) {
            $refererUrl = rtrim(strtok($this->redirect->getRefererUrl(), '?'), '/');
            $ledyerUrl = rtrim(
                $this->url->getUrl(ltrim(ConfigHelper::LEDYER_CHECKOUT_URL_PATH, '/')),
                '/'
            );

            if ($refererUrl !== $ledyerUrl) {
                return $proceed($carrierCode, $request);
            }

            if ($carrierCode === 'klarna_shipping_method_gateway') {
                return null;
            }
        }

        return $proceed($carrierCode, $request);
    }
}
