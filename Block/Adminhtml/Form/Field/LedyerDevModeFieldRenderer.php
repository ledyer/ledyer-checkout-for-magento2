<?php

namespace Ledyer\Payment\Block\Adminhtml\Form\Field;

class LedyerDevModeFieldRenderer extends \Magento\Config\Block\System\Config\Form\Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (getenv('LEDYER_MODE') !== 'developer') {
            return '';
        }

        return parent::render($element);
    }
}
