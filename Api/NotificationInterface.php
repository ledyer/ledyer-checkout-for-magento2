<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Api;

interface NotificationInterface
{

    /**
     * Process incoming notifications
     *
     * @param string $eventType
     * @param string $merchantId
     * @param string $orderId
     * @param string $reference
     * @param string $storeId
     * @return mixed
     */
    public function process($eventType, $merchantId, $orderId, $reference, $storeId);
}
