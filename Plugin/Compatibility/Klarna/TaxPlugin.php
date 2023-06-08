<?php

/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2023 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Plugin\Compatibility\Klarna;

use Klarna\Kco\Model\Tax;
use Ledyer\Payment\Helper\ConfigHelper;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;

class TaxPlugin
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
     * Disable tax recalculation by Klarna if user is in Ledyer checkout
     *
     * @param Tax $subject
     * @param callable $proceed
     * @param TaxDetailsItemInterface $taxDetailsItem
     * @return TaxDetailsItemInterface
     * @throws NoSuchEntityException
     */
    public function aroundUpdateMagentoTax(Tax $subject, callable $proceed, TaxDetailsItemInterface $taxDetailsItem)
    {
        if ($this->configHelper->getIsActive()) {
            $refererUrl = rtrim(strtok($this->redirect->getRefererUrl(), '?'), '/');
            $ledyerUrl = rtrim($this->url->getUrl(ltrim(ConfigHelper::LEDYER_CHECKOUT_URL_PATH, '/')), '/');
            if ($refererUrl === $ledyerUrl) {
                return $taxDetailsItem;
            }
        }

        return $proceed($taxDetailsItem);
    }
}
