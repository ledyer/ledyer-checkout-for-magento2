<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\Api;

use Exception;
use Ledyer\Payment\Api\ClientInterface;
use Ledyer\Payment\Api\MethodInterface;
use Ledyer\Payment\Helper\ConfigHelper;
use Ledyer\Payment\Logger\Logger;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\ClientFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class Client implements ClientInterface
{
    public const LEDYER_ACCESS_TOKEN = 'LEDYER_ACCESS_TOKEN';
    public const HOUR_IN_SECONDS = 3600;

    /**
     * @var ClientFactory
     */
    protected $client;

    /**
     * @var string
     */
    protected $response;
    /**
     * @var ConfigHelper
     */
    public $config;

    /**
     * @var CacheInterface
     */
    public $cache;

    /**
     * @var AuthRequest
     */
    public $auth;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Json
     */
    public $json;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param ConfigHelper $config
     * @param CacheInterface $cache
     * @param AuthRequest $auth
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     * @param ClientFactory $client
     * @param Logger $logger
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ConfigHelper $config,
        CacheInterface $cache,
        AuthRequest $auth,
        StoreManagerInterface $storeManager,
        Json $json,
        ClientFactory $client,
        Logger $logger,
        ManagerInterface $messageManager
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->auth = $auth;
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->client = $client->create();
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    /**
     * Make a request
     *
     * @param MethodInterface $request
     * @return void
     * @throws NoSuchEntityException
     */
    public function request(MethodInterface $request): void
    {
        $this->client->setHeaders(
            [
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken()),
                'Content-Type' => 'application/json'
            ]
        );
        switch ($request->getRequestType()) {
            case 'POST':
                $this->client->post($this->config->getApiUrl($request->getUrlPrefix(), $request->getEndpoint()),
                    $this->json->serialize($request->getBody())
                );
                $this->response = $this->client->getBody();
                $this->validateResponse($request, $this->client->getStatus(), $this->response);
                break;
            case 'GET':
                $this->client->get($this->config->getApiUrl($request->getUrlPrefix(), $request->getEndpoint()));
                $this->response = $this->client->getBody();
                $this->validateResponse($request, $this->client->getStatus(), $this->response);
                break;
            default:
                break;
        }
    }

    /**
     * Get request response
     *
     * @return ?string
     */
    public function response(): ?string
    {
        return $this->response;
    }

    /**
     * Get the access token for the authorization and cache it
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAccessToken(): string
    {
        if (!$this->cache->load(
            sprintf(
                '%s_%s',
                self::LEDYER_ACCESS_TOKEN,
                $this->storeManager->getStore()->getId()
            )
        )) {
            $response = $this->json->unserialize($this->authRequest());
            if (isset($response['access_token'])) {
                $this->cache->save(
                    $response['access_token'],
                    sprintf(
                        '%s_%s',
                        self::LEDYER_ACCESS_TOKEN,
                        $this->storeManager->getStore()->getId()
                    ),
                    [],
                    self::HOUR_IN_SECONDS
                );

                return $response['access_token'];
            } else {
                $this->messageManager->addErrorMessage(
                    'Something went wrong when sending an API request to Ledyer'
                );
            }
        }

        return $this->cache->load(
            sprintf(
                '%s_%s',
                self::LEDYER_ACCESS_TOKEN,
                $this->storeManager->getStore()->getId()
            )
        );
    }

    /**
     * Make auth request to get the access token
     *
     * @return string
     * @throws NoSuchEntityException
     */

    public function authRequest()
    {

        $this->client->setHeaders(
            [
                'Authorization' => sprintf('Basic %s', $this->getAuthToken()),
                'Content-Type' => $this->auth->getContentType()
            ]
        );
        $this->client->post($this->config->getApiUrl($this->auth->getUrlPrefix(), $this->auth->getEndpoint()),
            [
                'grant_type' => 'client_credentials'
            ]
        );
        $response = $this->client->getBody();
        $this->validateResponse($this->auth, $this->client->getStatus(), $response);
        return $response;
    }

    /**
     * Get base64 encoded auth token
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAuthToken()
    {
        return base64_encode(sprintf('%s:%s', $this->config->getClientId(), $this->config->getClientSecret()));
    }

    /**
     * Validates the response and logs it if the debug mode is enabled. Force logs if response status code unsuccessful
     *
     * @param MethodInterface $request
     * @param int $status
     * @param string $response
     * @return void
     */
    public function validateResponse(MethodInterface $request, int $status, string $response)
    {
        $response = $this->json->unserialize($response);
        if ($status < 200 || $status >= 300) {
            $logInput = [
                'response' => $response,
                'request' => [
                    'endpoint' => $request->getEndpoint(),
                    'requestType' => $request->getRequestType(),
                    'body' => $request->getBody()
                ]
            ];

            $this->logger->forceLogging('Client error: ', $logInput);
        } else {
            $logInput = [
                'endpoint' => $request->getEndpoint(),
                'requestType' => $request->getRequestType(),
                'body' => $request->getBody()
            ];
            $this->logger->logApiRequest($logInput);
            $this->logger->logApiResponse($response);
        }
    }
}
