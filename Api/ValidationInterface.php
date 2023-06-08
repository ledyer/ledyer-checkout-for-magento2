<?php

namespace Ledyer\Payment\Api;

interface ValidationInterface
{
    /**
     * Validate Ledyer session and Magento quote data
     *
     * @param string $merchantId
     * @param string $orderId
     * @param string $reference
     * @param string $storeId
     * @return string|bool
     */
    public function validate($merchantId, $orderId, $reference, $storeId);
}
