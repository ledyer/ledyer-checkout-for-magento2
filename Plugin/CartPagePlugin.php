<?php
/**
 * @category    Ledyer
 * @author      Oskars Elksnitis <info@scandiweb.com>
 * @package     Ledyer_Payment
 * @copyright   Copyright (c) 2022 Scandiweb, Ltd (https://scandiweb.com)
 */

namespace Ledyer\Payment\Plugin;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class CartPagePlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Session $checkoutSession
     * @param MessageManagerInterface $messageManager
     * @param QuoteRepository $quoteRepository
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $checkoutSession,
        MessageManagerInterface $messageManager,
        QuoteRepository $quoteRepository,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->quoteRepository = $quoteRepository;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * If quote has any validation errors, add them to the cart page
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeExecute()
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $errors = $quote->getLedyerValidationErrors();
            if ($errors) {
                if (is_array($this->json->unserialize($errors))) {
                    foreach ($this->json->unserialize($errors) as $error) {
                        $this->messageManager->addErrorMessage($error);
                    }
                } else {
                    $this->messageManager->addErrorMessage($errors);
                }
                $quote->setLedyerValidationErrors(null);
                $this->quoteRepository->save($quote);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [$e->getTraceAsString()]);
        }
    }
}
