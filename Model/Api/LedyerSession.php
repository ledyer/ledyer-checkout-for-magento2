<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model\Api;

use Ledyer\Payment\Helper\ConfigHelper;
use Ledyer\Payment\Model\QuoteRepository;
use Magento\Checkout\Model\Session as MageSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;

class LedyerSession
{
    public const ACTION_UPDATE = 'update';
    public const ACTION_CREATE = 'create';
    public const UNITS_IN_CURRENCY = 100;

    /**
     * @var MageSession
     */
    private $session;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ApiRequest
     */
    private $request;

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @var ConfigHelper
     */
    private $config;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var QuoteRepository
     */
    private $quote;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Calculation
     */
    private $calculator;

    /**
     * @var TimezoneInterface
     */
    private $time;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @param MageSession $session
     * @param Client $client
     * @param ApiRequest $request
     * @param Resolver $localeResolver
     * @param ConfigHelper $config
     * @param Json $json
     * @param QuoteRepository $quote
     * @param CustomerSession $customerSession
     * @param Calculation $calculator
     * @param TimezoneInterface $time
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        MageSession     $session,
        Client          $client,
        ApiRequest      $request,
        Resolver        $localeResolver,
        ConfigHelper    $config,
        Json            $json,
        QuoteRepository $quote,
        CustomerSession $customerSession,
        Calculation     $calculator,
        TimezoneInterface $time,
        UrlInterface    $urlInterface
    ) {
        $this->session = $session;
        $this->client = $client;
        $this->request = $request;
        $this->localeResolver = $localeResolver;
        $this->config = $config;
        $this->json = $json;
        $this->quote = $quote;
        $this->customerSession = $customerSession;
        $this->calculator = $calculator;
        $this->time = $time;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Send an API request to ledyer and update session data and ledyer quote
     *
     * @param string $endpoint
     * @param string $action
     * @return array|bool|float|int|mixed|string|null
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendRequest($endpoint, $action)
    {
        $quote = $this->session->getQuote();
        $quote->collectTotals();
        if ($quote->getGrandTotal() > 0) {
            // Set request parameters
            $this->request->setEndpoint($endpoint);
            $this->request->setRequestType('POST');
            $this->request->setBody($this->prepareData($quote, $action));

            // Send request to ledyer API
            $this->client->request($this->request);
            $data = $this->json->unserialize($this->client->response());
            if (isset($data['sessionId'])) {
                // Create ledyer quote, set session id and order id and save it
                if ($this->quote->checkIfQuoteExists($quote)) {
                    $ledyerQuote =  $this->quote->getQuoteByMageQuote($quote);
                } else {
                    $ledyerQuote = $this->quote->create();
                }
                $ledyerQuote->setLedyerSessionId($data['sessionId'])
                    ->setLedyerOrderId($data['orderId'])
                    ->setQuoteId($quote->getId());
                $this->quote->save($ledyerQuote);
                $this->session->setLedyerOrderId($data['orderId'])
                    ->setLedyerSessionExpiresAt($data['expiresAt'])
                    ->setLedyerSessionId($data['sessionId']);

                return $data;
            }
        }

        return null;
    }

    /**
     * Create ledyer session
     *
     * @return array
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function createSession()
    {
        if ($this->session->getLedyerSessionExpiresAt()) {
            $expiresAt = strtotime($this->session->getLedyerSessionExpiresAt());
            $currentTime = strtotime($this->time->date()->format('Y-m-d h:i:s'));
            if ($expiresAt > $currentTime && $this->session->getLedyerSessionId()) {
                return [
                    'sessionId' => $this->session->getLedyerSessionId(),
                    'orderId' => $this->session->getLedyerOrderId(),
                    'expiresAt' => $this->session->getLedyerSessionExpiresAt()
                ];
            }
        }

        return $this->sendRequest('v1/sessions', self::ACTION_CREATE);
    }

    /**
     * Update ledyer session
     *
     * @return JsonResult
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateSession()
    {
        if ($this->session->getLedyerOrderId() && $this->session->getLedyerSessionId()) {
            return $this->sendRequest(
                sprintf('v1/sessions/%s', $this->session->getLedyerOrderId()),
                self::ACTION_UPDATE
            );
        }
    }

    /**
     * Get ledyer order session
     *
     * @return array|bool|float|int|mixed|string|null
     * @throws NoSuchEntityException
     */
    public function getSession()
    {
        $this->request->setEndpoint(
            sprintf(
                'v1/sessions/%s',
                $this->session->getLedyerOrderId()
            )
        );
        $this->request->setRequestType('GET');
        $this->request->setBody([]);
        $this->client->request($this->request);

        return $this->json->unserialize($this->client->response());
    }

    /**
     * Get order lines for the API request
     *
     * @param CartInterface $quote
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderLines($quote)
    {
        $items = $quote->getAllVisibleItems();
        $orderLines = [];
        foreach ($items as $item) {
            if ($item->getProduct()->getTypeId() === 'bundle') {
                foreach ($item->getChildren() as $child) {
                    $orderLines[] = $this->getProductData($child, $item->getQty());
                }
            } else {
                $orderLines[] = $this->getProductData($item);
            }
        }
        if ($this->getShippingDetails($quote)) {
            $orderLines[] = $this->getShippingDetails($quote);
        }

        $orderLines = array_merge($orderLines, $this->getDiscountLines($quote));

        return $orderLines;
    }

    /**
     * Get discount lines
     *
     * @param CartInterface $quote
     * @return array
     */
    public function getDiscountLines($quote)
    {
        $vatAmounts = [];
        foreach ($quote->getAllItems() as $item) {
            if (!in_array($item->getTaxPercent(), $vatAmounts)) {
                $vatAmounts[] = $item->getTaxPercent();
            }
        }
        $discountLines = [];
        foreach ($vatAmounts as $amount) {
            $discountAmount = $this->getDiscountAmount($quote, $amount);
            if ($discountAmount > 0) {
                $discountLines[] = [
                    'reference' => 'Discount',
                    'description' => 'Discount',
                    'type' => 'discount',
                    'quantity' => 1,
                    'unitPrice' => 0,
                    'vat' => $amount * 100,
                    'totalAmount' => -$discountAmount,
                    'totalVatAmount' => -$this->getDiscountTaxCompensationAmount($quote, $amount) * 100,
                    'unitDiscountAmount' => $discountAmount
                ];
            }
        }

        return $discountLines;
    }

    /**
     * Get discount amount
     *
     * @param CartInterface $quote
     * @param int|float $vatAmount
     * @return int
     */
    public function getDiscountAmount($quote, $vatAmount)
    {
        $discountAmount = 0;
        $items = $quote->getAllItems();
        foreach ($items as $item) {
            if ($item->getTaxPercent() === $vatAmount) {
                $discountAmount += $item->getDiscountAmount();
            }
        }

        return $discountAmount * 100;
    }

    /**
     * Get product data array
     *
     * @param Item $item
     * @param int $qty
     * @return array
     */
    public function getProductData($item, $qty = null)
    {
        return [
            'type' => $this->getProductType($item),
            'reference' => $item->getSku(),
            'description' => $item->getName(),
            'quantity' => $qty ?? $item->getQty(),
            'unitPrice' => $item->getPriceInclTax() * 100,
            'vat' => $item->getTaxPercent() * 100,
            'unitDiscountAmount' => 0,
            'totalAmount' => $item->getRowTotalInclTax() * 100,
            'totalVatAmount' => ($item->getTaxAmount() + $item->getDiscountTaxCompensationAmount()) * 100
        ];
    }

    /**
     * Get shipping details if shipping is set in quote
     *
     * @param CartInterface $quote
     * @return array|null
     * @throws NoSuchEntityException
     */
    public function getShippingDetails($quote)
    {
        if ($quote->getShippingAddress()->getShippingMethod()) {
            $shippingDetails = $quote->getShippingAddress();
            $taxRateId = $this->config->getConfigValue(TaxConfig::CONFIG_XML_PATH_SHIPPING_TAX_CLASS);
            $request = $this->calculator->getRateRequest(
                $quote->getShippingAddress(),
                $quote->getBillingAddress(),
                $quote->getCustomerTaxClassId(),
                $quote->getStoreId()
            )->setProductClassId($taxRateId);
            $rate = $this->calculator->getRate($request);

            return [
                'type' => 'shippingFee',
                'reference' => $shippingDetails->getShippingMethod(),
                'description' => $shippingDetails->getShippingDescription(),
                'quantity' => 1,
                'unitPrice' => $shippingDetails->getShippingInclTax() * 100,
                'unitDiscountAmount' => $shippingDetails->getShippingDiscountAmount() * 100,
                'vat' => $rate * 100,
                'totalAmount' => ($shippingDetails->getShippingInclTax() -
                    $shippingDetails->getShippingDiscountAmount()) * 100,
                'totalVatAmount' => $shippingDetails->getShippingTaxAmount() * 100
            ];
        }

        return null;
    }
    /**
     * Prepare data for the api request
     *
     * @param CartInterface $quote
     * @param string $action
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareData($quote, $action)
    {
        $totalVatSum = $this->sumTotalVat($this->getOrderLines($quote)) / 100;
        $data = [
            'country' => $this->config->getConfigValue('general/store_information/country_id'),
            'currency' => $quote->getQuoteCurrencyCode(),
            'locale' => $this->localeResolver->getLocale(),
            'orderLines' => $this->getOrderLines($quote),
            'settings' => $this->getSettings($action),
            'totalOrderAmount' => $quote->getGrandTotal() * 100,
            'totalOrderAmountExclVat' => ($quote->getGrandTotal() - $totalVatSum) * 100,
            'totalOrderVatAmount' => $totalVatSum * 100
        ];
        if ($action === self::ACTION_CREATE) {
            $data['customer'] = $this->getCustomerData($quote);
        }

        return $data;
    }

    /**
     * Get discount compensated tax amount
     *
     * Since magento calculates and rounds compensation value for each item
     * recalculate manually like gateway would do and compare with sum of compensations.
     * If difference is larger than API allows, adjust calculated value to match.
     *
     * @param CartInterface $quote
     * @param int|float $vatPercent
     * @return mixed
     */
    public function getDiscountTaxCompensationAmount($quote, $vatPercent)
    {
        $vatAmount = 0;
        $discount = $this->getDiscountAmount($quote, $vatPercent);
        foreach ($quote->getAllItems() as $item) {
            if ($item->getTaxPercent() === $vatPercent) {
                $vatAmount += $item->getBaseDiscountTaxCompensationAmount();
            }
        }

        // Must be within ±1 of total_amount - total_amount * 10000 / (10000 + vat).
        $scaledVatAmount = $vatAmount * self::UNITS_IN_CURRENCY;
        $diff = $scaledVatAmount - ($discount - $discount * (10000 / (10000 + $vatPercent * self::UNITS_IN_CURRENCY)));
        if ($diff > 1 || $diff < -1) {
            $vatAmount -= round($diff) / self::UNITS_IN_CURRENCY;
        }

        return number_format($vatAmount, 2);
    }

    /**
     * Get customer details if user is logged in
     *
     * @param CartInterface $quote
     * @return array|null
     */
    public function getCustomerData($quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            return [
                'firstName' => $customer->getFirstname(),
                'lastName' => $customer->getLastname(),
                'email' => $customer->getEmail(),
                'phone' => $quote->getShippingAddress()->getTelephone(),
                'companyId' => $customer->getCompanyId(),
                'reference1' => $customer->getReference1(),
                'reference2' => $customer->getReference2()
            ];
        }

        return null;
    }
    /**
     * Get product type
     *
     * @param Item $item
     * @return string|void
     */
    public function getProductType($item)
    {
        $product = $item->getProduct();
        $productType = $product->getTypeId();
        switch ($productType) {
            case 'downloadable':
                return 'digital';
            case 'virtual':
                return 'service';
            case 'simple' || 'configurable':
                return 'physical';
        }
    }

    /**
     * Get iFrame settings from config
     *
     * @param string $action
     * @return array[]
     * @throws NoSuchEntityException
     */
    public function getSettings($action)
    {
        if ($action === self::ACTION_CREATE) {
            $settings = [
                'security' => [
                    'level' => $this->config->getSecurityLevel()
                ],
                'urls' => [
                    'terms' => $this->config->getTermsUrl(),
                    'privacy' => $this->config->getPrivacyUrl(),
                    'validate' => $this->getValidationUrl()
                ],
                'customer' => [
                    'showNameFields' => true,
                    'allowShippingAddress' => $this->config->getAllowShippingAddress()
                ]
            ];
        } else {
            $settings = [
                'security' => [
                    'level' => $this->config->getSecurityLevel()
                ]
            ];
        }

        return $settings;
    }

    /**
     * Get validation URL
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return sprintf(
            '%s%s',
            $this->urlInterface->getBaseUrl(),
            'rest/V1/ledyer/validate'
        );
    }

    /**
     * Iterate through orderlines and sum total vat amounts
     *
     * @param array $orderLines
     *
     * @return float
     */
    private function sumTotalVat(array $orderLines): float
    {
        $totalVat = 0.0;
        foreach ($orderLines as $line) {
            $totalVat+= $line['totalVatAmount'];
        }

        return (float) $totalVat;
    }
}
