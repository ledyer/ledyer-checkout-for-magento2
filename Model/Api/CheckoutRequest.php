<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\Api;

class CheckoutRequest extends AbstractMethod
{
    public const URL_PREFIX = 'checkout';

    /**
     * @var string
     */
    public $endpointUrl;

    /**
     * @var string
     */
    public $requestType;

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return $this->endpointUrl;
    }

    /**
     * Set request type
     *
     * @param string $type
     * @return void
     */
    public function setRequestType($type)
    {
        $this->requestType = $type;
    }

    /**
     * @inheritDoc
     */
    public function getRequestType(): string
    {
        return $this->requestType;
    }

    /**
     * Set api endpoint
     *
     * @param string $url
     * @return void
     */
    public function setEndpoint($url)
    {
        $this->endpointUrl = $url;
    }

    /**
     * @inheritDoc
     */
    public function getUrlPrefix(): string
    {
        return self::URL_PREFIX;
    }
}
