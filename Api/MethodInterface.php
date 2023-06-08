<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Api;

interface MethodInterface
{
    /**
     * Set body of the request
     *
     * @param array $body
     * @return void
     */
    public function setBody(array $body): void;

    /**
     * Get request body
     *
     * @return array
     */
    public function getBody(): array;

    /**
     * Get request endpoint
     *
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * Get request type
     *
     * @return string
     */
    public function getRequestType(): string;

    /**
     * Get api URL prefix
     *
     * @return string
     */
    public function getUrlPrefix(): string;
}
