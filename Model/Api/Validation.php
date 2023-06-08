<?php

namespace Ledyer\Payment\Model\Api;

use Ledyer\Payment\Api\ValidationInterface;
use Ledyer\Payment\Model\QuoteRepository as LedyerQuoteRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Response;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\ValidationRules\QuoteValidationComposite;

class Validation implements ValidationInterface
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var LedyerSession
     */
    private $ledyerSession;

    /**
     * @var LedyerQuoteRepository
     */
    private $ledyerQuoteRepository;

    /**
     * @var ApiRequest
     */
    private $request;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var QuoteValidationComposite
     */
    private $quoteValidationComposite;

    /**
     * @param Response $response
     * @param UrlInterface $urlInterface
     * @param LedyerSession $ledyerSession
     * @param ApiRequest $request
     * @param LedyerQuoteRepository $ledyerQuoteRepository
     * @param QuoteRepository $quoteRepository
     * @param Client $client
     * @param Json $json
     * @param QuoteValidationComposite $quoteValidationComposite
     */
    public function __construct(
        Response                 $response,
        UrlInterface             $urlInterface,
        LedyerSession            $ledyerSession,
        ApiRequest               $request,
        LedyerQuoteRepository    $ledyerQuoteRepository,
        QuoteRepository          $quoteRepository,
        Client                   $client,
        Json                     $json,
        QuoteValidationComposite $quoteValidationComposite
    ) {
        $this->response = $response;
        $this->urlInterface = $urlInterface;
        $this->ledyerSession = $ledyerSession;
        $this->request = $request;
        $this->ledyerQuoteRepository = $ledyerQuoteRepository;
        $this->quoteRepository = $quoteRepository;
        $this->client = $client;
        $this->json = $json;
        $this->quoteValidationComposite = $quoteValidationComposite;
    }

    /**
     * @inheritDoc
     */
    public function validate($merchantId, $orderId, $reference, $storeId)
    {
        if (!$this->compareOrders($orderId)) {
            $this->response->setHttpResponseCode(303);
            $this->response->setHeader('location', $this->urlInterface->getUrl('checkout/cart'));
            return;
        }
        $this->response->setHttpResponseCode(200);
    }

    /**
     * Compare Ledyer order data with Magento
     *
     * @param string $orderId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function compareOrders($orderId)
    {
        $ledyerQuote = $this->ledyerQuoteRepository->loadQuote('ledyer_order_id', $orderId);
        $quote = $this->quoteRepository->get($ledyerQuote->getQuoteId());
        $errors = [];
        $validationResults = $this->quoteValidationComposite->validate($quote);
        foreach ($validationResults as $validationResult) {
            foreach ($validationResult->getErrors() as $error) {
                if ($error->render() !== 'Enter a valid payment method and try again.') {
                    $errors[] = $error;
                }
            }
        }
        if ($errors) {
            $quote->setLedyerValidationErrors($this->json->serialize($errors));
            $this->quoteRepository->save($quote);

            return false;
        }

        $ledyerSession = $this->getSession($quote);
        $customerData = $ledyerSession['customer'];
        $mageBillingAddress = $quote->getBillingAddress();
        if ($customerData['firstName'] !== $mageBillingAddress->getfirstname() ||
            $customerData['lastName'] !== $mageBillingAddress->getLastname() ||
            $customerData['email'] !== $mageBillingAddress->getEmail() ||
            $customerData['phone'] !== $mageBillingAddress->getTelephone() ||
            $customerData['billingAddress']['companyName'] !== $mageBillingAddress->getCompany() ||
            ($customerData['billingAddress']['streetAddress'] !== '' &&
                $customerData['billingAddress']['streetAddress'] !== $mageBillingAddress->getStreet()[0]) ||
            str_replace(
                ' ',
                '',
                $customerData['billingAddress']['postalCode']
            ) !== $mageBillingAddress->getPostcode() ||
            $customerData['billingAddress']['city'] !== $mageBillingAddress->getCity() ||
            $customerData['billingAddress']['country'] !== $mageBillingAddress->getCountry()
        ) {
            $quote->setLedyerValidationErrors(
                $this->json->serialize(__("Billing address doesn't match with Ledyer billing address"))
            );
            $this->quoteRepository->save($quote);

            return false;
        }
        if (!$quote->isVirtual()) {
            $mageShippingAddress = $quote->getShippingAddress();
            if ($customerData['firstName'] !== $mageShippingAddress->getfirstname() ||
                $customerData['lastName'] !== $mageShippingAddress->getLastname() ||
                $customerData['email'] !== $mageShippingAddress->getEmail() ||
                $customerData['phone'] !== $mageShippingAddress->getTelephone() ||
                $customerData['shippingAddress']['companyName'] !== $mageShippingAddress->getCompany() ||
                ($customerData['shippingAddress']['streetAddress'] !== '' &&
                    $customerData['shippingAddress']['streetAddress'] !== $mageShippingAddress->getStreet()[0]) ||
                str_replace(
                    ' ',
                    '',
                    $customerData['shippingAddress']['postalCode']
                ) !== $mageShippingAddress->getPostcode() ||
                $customerData['shippingAddress']['city'] !== $mageShippingAddress->getCity() ||
                $customerData['shippingAddress']['country'] !== $mageShippingAddress->getCountry()
            ) {
                $quote->setLedyerValidationErrors(
                    $this->json->serialize(__("Shipping address doesn't match with Ledyer address"))
                );
                $this->quoteRepository->save($quote);

                return false;
            }
        }

        $ledyerOrderLines = $ledyerSession['order']['orderLines'];
        $orderLines = $this->ledyerSession->getOrderLines($quote);
        // Json encode and then decode the orderlines so that they are the same as the data sent to the API
        $orderLines = $this->json->unserialize($this->json->serialize($orderLines));
        foreach ($orderLines as $keyOne => $orderLine) {
            foreach ($orderLine as $keyTwo => $value) {
                if (!isset($ledyerOrderLines[$keyOne][$keyTwo])) {
                    $quote->setLedyerValidationErrors(
                        $this->json->serialize(__('Order validation failed - missing order lines'))
                    );
                    $this->quoteRepository->save($quote);
                    return false;
                }
                if ($value != $ledyerOrderLines[$keyOne][$keyTwo]) {
                    $quote->setLedyerValidationErrors(
                        $this->json->serialize(
                            __(
                                'Order validation failed, %1 doesn\'t match',
                                $keyTwo
                            )
                        )
                    );
                    $this->quoteRepository->save($quote);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get Ledyer session
     *
     * @param CartInterface $quote
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getSession($quote)
    {
        $ledyerQuote = $this->ledyerQuoteRepository->getQuoteByMageQuote($quote);
        $this->request->setEndpoint(
            sprintf(
                'v1/sessions/%s',
                $ledyerQuote->getLedyerOrderId()
            )
        );
        $this->request->setRequestType('GET');
        $this->request->setBody([]);
        $this->client->request($this->request);

        return $this->json->unserialize($this->client->response());
    }
}
