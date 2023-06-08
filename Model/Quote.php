<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Quote extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'ledyer_quote';

    /**
     * Get identities
     *
     * @return array[]
     */
    public function getIdentities()
    {
        return [sprintf('%s_%s', self::CACHE_TAG, $this->getId())];
    }

    /**
     * Set Ledyer order Id
     *
     * @param int $orderId
     * @return $this
     */
    public function setLedyerOrderId($orderId)
    {
        $this->setData('ledyer_order_id', $orderId);

        return $this;
    }

    /**
     * Get Ledyer order Id
     *
     * @return array|mixed|null
     */
    public function getLedyerOrderId()
    {
        return $this->getData('ledyer_order_id');
    }

    /**
     * Set Ledyer session Id
     *
     * @param string $sessionId
     * @return $this
     */
    public function setLedyerSessionId($sessionId)
    {
        $this->setData('ledyer_session_id', $sessionId);

        return $this;
    }

    /**
     * Get Ledyer session Id
     *
     * @return array|mixed|null
     */
    public function getLedyerSessionId()
    {
        return $this->getData('ledyer_session_id');
    }

    /**
     * Set Magento quote Id
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        $this->setData('quote_id', $quoteId);

        return $this;
    }

    /**
     * Get Magento quote Id
     *
     * @return array|mixed|null
     */
    public function getQuoteId()
    {
        return $this->getData('quote_id');
    }

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Quote::class);
    }
}
