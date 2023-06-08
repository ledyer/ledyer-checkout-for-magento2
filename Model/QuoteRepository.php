<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Model;

use Exception;
use Ledyer\Payment\Model\ResourceModel\Quote as QuoteResource;
use Ledyer\Payment\Model\Quote;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface as MageQuoteInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuoteRepository
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteResource
     */
    protected $resourceModel;

    /**
     * Class constructor
     *
     * @param QuoteFactory $quoteFactory
     * @param QuoteResource $resourceModel
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $resourceModel
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Create quote object
     *
     * @param array $data
     * @return Quote
     */
    public function create($data = [])
    {
        return $this->quoteFactory->create($data);
    }

    /**
     * Save quote
     *
     * @param Quote $quote
     * @return QuoteResource
     * @throws CouldNotSaveException
     */
    public function save(Quote $quote)
    {
        try {
            return $this->resourceModel->save($quote);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * Delete quote
     *
     * @param Quote $quote
     * @return QuoteResource
     * @throws Exception
     */
    public function delete(Quote $quote)
    {
        return $this->resourceModel->delete($quote);
    }

    /**
     * Delete quote by Id
     *
     * @param int $id
     * @return QuoteResource
     * @throws Exception
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * Load quote
     *
     * @param string $field
     * @param mixed $identifier
     * @return Quote
     * @throws NoSuchEntityException
     */
    public function loadQuote($field, $identifier)
    {
        $quote = $this->quoteFactory->create();
        $this->resourceModel->load($quote, $identifier, $field);
        if (!$quote->getId()) {
            throw NoSuchEntityException::singleField($field, $identifier);
        }
        return $quote;
    }

    /**
     * Get Ledyer quote by Magento quote object
     *
     * @param MageQuoteInterface $mageQuote
     * @return Quote
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getQuoteByMageQuote(MageQuoteInterface $mageQuote)
    {
        $quoteId = $this->resourceModel->getQuoteByMageQuote($mageQuote);
        if (!$quoteId) {
            throw NoSuchEntityException::singleField('quote_id', $mageQuote->getId());
        }
        return $this->loadQuote('ledyer_quote_id', $quoteId);
    }

    /**
     * Check if Ledyer quote exists for magento quote
     *
     * @param MageQuoteInterface $mageQuote
     * @return bool
     * @throws LocalizedException
     */
    public function checkIfQuoteExists(MageQuoteInterface $mageQuote)
    {
        if ($this->resourceModel->getQuoteByMageQuote($mageQuote)) {
            return true;
        }

        return false;
    }
    /**
     * Get quote by Id
     *
     * @param int $id
     * @return Quote
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        return $this->loadQuote('ledyer_quote_id', $id);
    }
}
