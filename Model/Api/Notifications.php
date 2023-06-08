<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\Api;

use Ledyer\Payment\Api\NotificationInterface;
use Ledyer\Payment\Logger\Logger;
use Ledyer\Payment\Model\Api\LedyerOrder as OrderApi;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\Serialize\Serializer\Json;

class Notifications implements NotificationInterface
{
    public const EVENT_CREATE = 'com.ledyer.order.create';
    public const EVENT_READY_FOR_CAPTURE = 'com.ledyer.order.ready_for_capture';
    public const EVENT_FULL_CAPTURE = 'com.ledyer.order.full_capture';
    public const EVENT_PARTIAL_CAPTURE = 'com.ledyer.order.part_capture';
    public const EVENT_FULL_REFUND = 'com.ledyer.order.full_refund';
    public const EVENT_PARTIAL_REFUND = 'com.ledyer.order.part_refund';
    public const EVENT_CANCEL = 'com.ledyer.order.cancel';

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var OrderApi
     */
    private $orderApi;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Logger $logger
     * @param CollectionFactory $collectionFactory
     * @param LedyerOrder $orderApi
     * @param Json $json
     */
    public function __construct(
        Logger $logger,
        CollectionFactory $collectionFactory,
        OrderApi $orderApi,
        Json $json
    ) {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->orderApi = $orderApi;
        $this->json = $json;
    }

    /**
     * @inheritDoc
     */
    public function process($eventType, $merchantId, $orderId, $reference, $storeId)
    {
        $this->logger->logNotification(
            'Notification received: ',
            [
            'eventType' => $eventType,
            'merchantId' => $merchantId,
            'orderId' => $orderId,
            'reference' => $reference,
            'storeId' => $storeId
            ]
        );

        $shouldAcknowledge = false;
        $order = $this->getOrder($orderId);
        switch ($eventType) {
            case self::EVENT_CREATE:
                    $order->addCommentToStatusHistory('Ledyer: Order has been created');
                    $shouldAcknowledge = true;
                break;
            case self::EVENT_READY_FOR_CAPTURE:
                    $order->addCommentToStatusHistory('Ledyer: Order is ready for Capture');
                    $order->setState(Order::STATE_PROCESSING)
                        ->setStatus(Order::STATE_PROCESSING);
                    $shouldAcknowledge = true;
                break;
        }
        $order->save();

        if ($shouldAcknowledge) {
            $this->orderApi->acknowledge($orderId);

            $apiOrder = $this->orderApi->getOrder($order);
            if (!$apiOrder['merchantReference']) {
                $this->orderApi->setReference($orderId, $order->getIncrementId());
            }
        }

        return $this->json->serialize(['success' => 'Notification was processed']);
    }

    /**
     * Get order by ledyer order id
     *
     * @param string $orderId
     * @return DataObject|Order
     * @throws NoSuchEntityException
     */
    public function getOrder($orderId)
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('ledyer_order_id', ['eq' => $orderId]);
        if (!$collection->getItems()) {
            throw new NoSuchEntityException(__('The order doesn\'t exist'));
        }

        return $collection->getFirstItem();
    }
}
