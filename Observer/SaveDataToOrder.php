<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Observer;

use Ledyer\Payment\Model\Payment\LedyerPayment;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Ledyer\Payment\Model\QuoteRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveDataToOrder implements ObserverInterface
{
    /**
     * @var QuoteRepository
     */
    private QuoteRepository $quote;

    /**
     * @param QuoteRepository $quote
     */
    public function __construct(
        QuoteRepository $quote
    ) {
        $this->quote = $quote;
    }

    /**
     * Save ledyer order and session IDs in the sales_order table
     *
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $quoteShippingAddress = $quote->getShippingAddress();
        $quoteBillingAddress = $quote->getBillingAddress();
        $orderShippingAddress = $order->getShippingAddress();
        $orderBillingAddress = $order->getBillingAddress();
        if ($order->getPayment()->getMethod() === LedyerPayment::METHOD_CODE) {
            $ledyerQuote = $this->quote->getQuoteByMageQuote($quote);
            $order->setLedyerSessionId($ledyerQuote->getLedyerSessionId());
            $order->setLedyerOrderId($ledyerQuote->getLedyerOrderId());
            if ($orderShippingAddress) {
                $orderShippingAddress->setLedyerCareOf($quoteShippingAddress->getLedyerCareOf());
                $orderShippingAddress->setLedyerAttentionName($quoteShippingAddress->getLedyerAttentionName());
            }
            $orderBillingAddress->setLedyerCareOf($quoteBillingAddress->getLedyerCareOf());
            $orderBillingAddress->setLedyerAttentionName($quoteBillingAddress->getLedyerAttentionName());
        }

        return $this;
    }
}
