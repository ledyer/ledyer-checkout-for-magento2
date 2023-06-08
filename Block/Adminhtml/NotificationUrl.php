<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;
use Magento\Config\Block\System\Config\Form\Field;

class NotificationUrl extends Field
{
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @param Context $context
     * @param UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlInterface,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlInterface = $urlInterface;
    }

    /**
     * Render the note attribute
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = sprintf(
            '<td class="label">Notification Endpoint</td>
                    <td class="value">%s
                        <p class="note">
                            <span>Endpoint for Ledyer notifications</span>
                        </p>
                    </td>',
            $this->getNotificationUrl()
        );

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Get notification url
     *
     * @return string
     */
    public function getNotificationUrl()
    {
        $baseUrl = $this->urlInterface->getBaseUrl();

        return sprintf('%s%s', $baseUrl, 'rest/V1/ledyer/notifications');
    }
}
