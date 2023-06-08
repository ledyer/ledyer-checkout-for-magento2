<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model;

use Magento\Framework\Data\OptionSourceInterface;

class SecurityLevels implements OptionSourceInterface
{
    /**
     * Returns available security levels for ledyer checkout
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = [100, 110, 120, 200, 210, 220, 300];
        $options = [];
        foreach ($availableOptions as $option) {
            $options[] = [
                'value' => $option,
                'label' => $option
            ];
        }

        return $options;
    }
}
