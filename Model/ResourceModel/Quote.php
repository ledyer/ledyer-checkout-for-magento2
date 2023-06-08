<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Quote\Api\Data\CartInterface;

class Quote extends AbstractDb
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ledyer_quote', 'ledyer_quote_id');
    }

    /**
     * Get Ledyer quote by Magento quote
     *
     * @param CartInterface $mageQuote
     * @return string
     * @throws LocalizedException
     */
    public function getQuoteByMageQuote(CartInterface $mageQuote)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), 'ledyer_quote_id')
            ->where('quote_id = :quote_id');
        $bind = [':quote_id' => (string)$mageQuote->getId()];

        return $connection->fetchOne($select, $bind);
    }
}
