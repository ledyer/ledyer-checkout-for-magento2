<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\Api;

class AuthRequest extends AbstractMethod
{
    public const ENDPOINT_URL = 'oauth/token';
    public const ENDPOINT_TYPE = 'POST';
    public const CONTENT_TYPE = 'application/x-www-form-urlencoded';
    public const URL_PREFIX = 'auth';

    /**
     * @inheritDoc
     */
    public function getEndpoint(): string
    {
        return self::ENDPOINT_URL;
    }

    /**
     * @inheritDoc
     */
    public function getRequestType(): string
    {
        return self::ENDPOINT_TYPE;
    }

    /**
     * Get content type
     *
     * @return string
     */
    public function getContentType(): string
    {
        return self::CONTENT_TYPE;
    }

    /**
     * @inheritDoc
     */
    public function getUrlPrefix(): string
    {
        return self::URL_PREFIX;
    }
}
