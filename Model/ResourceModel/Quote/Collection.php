<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ledyer\Payment\Model\Quote as QuoteModel;
use Ledyer\Payment\Model\ResourceModel\Quote as QuoteResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Construct method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QuoteModel::class, QuoteResourceModel::class);
    }
}
