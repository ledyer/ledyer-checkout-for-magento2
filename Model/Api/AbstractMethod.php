<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\Api;

use Ledyer\Payment\Api\MethodInterface;

abstract class AbstractMethod implements MethodInterface
{
    /**
     * @var array
     */
    protected $body = [];

    /**
     * @inheritDoc
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    /**
     * @inheritDoc
     */
    abstract public function getEndpoint(): string;

    /**
     * @inheritDoc
     */
    abstract public function getRequestType(): string;

    /**
     * @inheritDoc
     */
    abstract public function getUrlPrefix(): string;
}
