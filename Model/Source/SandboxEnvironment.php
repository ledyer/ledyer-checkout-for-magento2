<?php

namespace Ledyer\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class SandboxEnvironment implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (getenv('LEDYER_MODE') === 'developer') {
            return [
                ['value' => 'sandbox', 'label' => __('Sandbox')],
                ['value' => 'dev', 'label' => __('Dev')],
                ['value' => 'local', 'label' => __('Local')]
            ];
        }

        return [];
    }
}
