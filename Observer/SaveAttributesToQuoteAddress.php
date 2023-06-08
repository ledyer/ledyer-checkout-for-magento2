<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveAttributesToQuoteAddress implements ObserverInterface
{
    /**
     * Save Care Of and Attention fields to the address
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($address = $observer->getEvent()->getQuoteAddress()) {
            if ($attributes = $address->getExtensionAttributes()) {
                $address->setLedyerCareOf($attributes->getCareOf());
                $address->setLedyerAttentionName($attributes->getAttentionName());
            }
        }
    }
}
