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
use Magento\Checkout\Model\Session;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(
        Session $session
    ) {
        $this->session = $session;
    }

    /**
     * Clear ledyer session id and order id from magento session
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getPayment()->getMethod() === LedyerPayment::METHOD_CODE) {
            $this->session->unsLedyerSessionId();
            $this->session->unsLedyerOrderId();
            $this->session->unsLedyerSessionExpiresAt();
        }

        return $this;
    }
}
