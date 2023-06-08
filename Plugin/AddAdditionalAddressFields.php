<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Magento\Sales\Model\Order\Address;

class AddAdditionalAddressFields
{
    /**
     * Add Care Of and Attention fields to the addresses in order overview
     *
     * @param Info $subject
     * @param null|string $result
     * @param Address $address
     * @return string
     */
    public function afterGetFormattedAddress(Info $subject, $result, $address)
    {
        if ($careOf = $address->getLedyerCareOf()) {
            $result .= "<br/>c/o: $careOf<br/>";
        }
        if ($attention = $address->getLedyerAttentionName()) {
            $result .= "<br/>Attention: $attention<br/>";
        }

        return $result;
    }
}
