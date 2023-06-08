<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Plugin;

use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Invoice;
use Ledyer\Payment\Helper\ConfigHelper;

class DisableInvoiceEmail
{
    /**
     * Disable invoice email for orders with ledyer payment method
     *
     * @param InvoiceSender $subject
     * @param callable $proceed
     * @param Invoice $invoice
     * @param bool $forceSyncMod
     * @return bool
     */
    public function aroundSend(
        InvoiceSender $subject,
        callable $proceed,
        Invoice $invoice,
        $forceSyncMod = false
    ) {
        if ($invoice->getOrder()->getPayment()->getMethod() !== ConfigHelper::LEDYER_METHOD_CODE) {
            return $proceed($invoice, $forceSyncMod);
        }

        return false;
    }
}
