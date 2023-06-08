<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Framework\Serialize\Serializer\Json;

class LedyerOrder
{
    /**
     * @var ApiRequest
     */
    private $request;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param ApiRequest $request
     * @param Client $client
     * @param Json $json
     */
    public function __construct(
        ApiRequest $request,
        Client $client,
        Json $json
    ) {
        $this->request = $request;
        $this->client = $client;
        $this->json = $json;
    }

    /**
     * Send order acknowledgment request to ledyer
     *
     * @param string $orderId
     * @return void
     * @throws NoSuchEntityException
     */
    public function acknowledge($orderId)
    {
        $this->request->setEndpoint(
            sprintf(
                'v1/orders/%s/acknowledge',
                $orderId
            )
        );
        $this->request->setRequestType('POST');
        $this->request->setBody([]);
        $this->client->request($this->request);
    }

    /**
     * Update order reference.
     *
     * @param string $orderId
     * @param string $reference
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function setReference(string $orderId, string $reference): void
    {
        $this->request->setEndpoint(
            sprintf(
                'v1/orders/%s/reference',
                $orderId
            )
        );
        $this->request->setRequestType('POST');
        $this->request->setBody(
            ['reference' => $reference]
        );
        $this->client->request($this->request);
    }

    /**
     * Get order from ledyer
     *
     * @param Order $order
     * @return string|null
     * @throws NoSuchEntityException
     */
    public function getOrder($order)
    {
        $this->request->setEndpoint(
            sprintf(
                'v1/orders/%s',
                $order->getLedyerOrderId()
            )
        );
        $this->request->setRequestType('GET');
        $this->request->setBody([]);
        $this->client->request($this->request);

        return $this->json->unserialize($this->client->response());
    }
}
