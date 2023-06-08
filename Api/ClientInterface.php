<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Api;

use Psr\Http\Message\ResponseInterface;

interface ClientInterface
{
    /**
     * Make a request
     *
     * @param MethodInterface $request
     */
    public function request(MethodInterface $request): void;

    /**
     * Get response from the request
     *
     * @return ?string
     */
    public function response(): ?string;
}
