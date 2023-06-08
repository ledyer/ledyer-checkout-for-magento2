<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Backend\Block\Widget\Context;

class LedyerInfo extends Template
{
    /**
     * @var OrderInterface
     */
    private $orderInterface;

    /**
     * Construct method
     *
     * @param Context $context
     * @param OrderInterface $orderInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrderInterface $orderInterface,
        array $data = []
    ) {
        $this->orderInterface = $orderInterface;
        parent::__construct($context, $data);
    }

    /**
     * Get Ledyer Session Id
     *
     * @return mixed
     */
    public function getSessionId()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderInterface->load($orderId);

        return $order->getLedyerSessionId();
    }

    /**
     * Get Ledyer Order Id
     *
     * @return mixed
     */
    public function getOrderId()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderInterface->load($orderId);

        return $order->getLedyerOrderId();
    }
}
