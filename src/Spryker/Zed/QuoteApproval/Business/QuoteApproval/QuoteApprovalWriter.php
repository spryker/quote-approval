<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteApproval\Business\QuoteApproval;

use ArrayObject;
use Generated\Shared\Transfer\QuoteApprovalRequestTransfer;
use Generated\Shared\Transfer\QuoteApprovalResponseTransfer;
use Generated\Shared\Transfer\QuoteApprovalTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Shared\QuoteApproval\QuoteApprovalConfig;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\QuoteApproval\Business\Quote\QuoteLockerInterface;
use Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalEntityManagerInterface;
use Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalRepositoryInterface;

class QuoteApprovalWriter implements QuoteApprovalWriterInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\QuoteApproval\Business\QuoteApproval\QuoteApprovalRequestValidatorInterface
     */
    protected $quoteApprovalRequestValidator;

    /**
     * @var \Spryker\Zed\QuoteApproval\Business\QuoteApproval\QuoteApprovalMessageBuilderInterface
     */
    protected $quoteApprovalMessageBuilder;

    /**
     * @var \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalEntityManagerInterface
     */
    protected $quoteApprovalEntityManager;

    /**
     * @var \Spryker\Zed\QuoteApproval\Business\Quote\QuoteLockerInterface
     */
    protected $quoteLocker;

    /**
     * @var \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalRepositoryInterface
     */
    protected $quoteApprovalRepository;

    public function __construct(
        QuoteApprovalRequestValidatorInterface $quoteApprovalRequestValidator,
        QuoteApprovalMessageBuilderInterface $quoteApprovalMessageBuilder,
        QuoteApprovalEntityManagerInterface $quoteApprovalEntityManager,
        QuoteLockerInterface $quoteLocker,
        QuoteApprovalRepositoryInterface $quoteApprovalRepository
    ) {
        $this->quoteApprovalRequestValidator = $quoteApprovalRequestValidator;
        $this->quoteApprovalMessageBuilder = $quoteApprovalMessageBuilder;
        $this->quoteApprovalEntityManager = $quoteApprovalEntityManager;
        $this->quoteLocker = $quoteLocker;
        $this->quoteApprovalRepository = $quoteApprovalRepository;
    }

    public function approveQuoteApproval(QuoteApprovalRequestTransfer $quoteApprovalRequestTransfer): QuoteApprovalResponseTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(function () use ($quoteApprovalRequestTransfer) {
            return $this->executeApproveQuoteApprovalTransaction($quoteApprovalRequestTransfer);
        });
    }

    public function declineQuoteApproval(QuoteApprovalRequestTransfer $quoteApprovalRequestTransfer): QuoteApprovalResponseTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(function () use ($quoteApprovalRequestTransfer) {
            return $this->executeDeclineQuoteApprovalTransaction($quoteApprovalRequestTransfer);
        });
    }

    protected function executeDeclineQuoteApprovalTransaction(QuoteApprovalRequestTransfer $quoteApprovalRequestTransfer): QuoteApprovalResponseTransfer
    {
        $quoteApprovalResponseTransfer = $this->quoteApprovalRequestValidator
            ->validateQuoteApprovalRequest($quoteApprovalRequestTransfer);

        if (!$quoteApprovalResponseTransfer->getIsSuccessful()) {
            return $this->createNotSuccessfulQuoteApprovalResponseTransfer(
                $quoteApprovalResponseTransfer->getMessages(),
            );
        }

        $this->quoteLocker->unlockQuote($quoteApprovalResponseTransfer->getQuote());

        $quoteApprovalTransfer = $this->updateQuoteApprovalWithStatus(
            $quoteApprovalResponseTransfer->getQuoteApproval(),
            QuoteApprovalConfig::STATUS_DECLINED,
        );
        $quoteTransfer = $this->replaceQuoteApprovalInQuote(
            $quoteApprovalResponseTransfer->getQuote(),
            $quoteApprovalTransfer,
        );

        return $this->createSuccessfulQuoteApprovalResponseTransfer($quoteApprovalTransfer)
            ->setQuote($quoteTransfer);
    }

    protected function executeApproveQuoteApprovalTransaction(QuoteApprovalRequestTransfer $quoteApprovalRequestTransfer): QuoteApprovalResponseTransfer
    {
        $quoteApprovalResponseTransfer = $this->quoteApprovalRequestValidator
            ->validateQuoteApprovalRequest($quoteApprovalRequestTransfer);

        if (!$quoteApprovalResponseTransfer->getIsSuccessful()) {
            return $this->createNotSuccessfulQuoteApprovalResponseTransfer(
                $quoteApprovalResponseTransfer->getMessages(),
            );
        }

        $quoteApprovalTransfer = $this->updateQuoteApprovalWithStatus(
            $quoteApprovalResponseTransfer->getQuoteApproval(),
            QuoteApprovalConfig::STATUS_APPROVED,
        );
        $quoteTransfer = $this->replaceQuoteApprovalInQuote(
            $quoteApprovalResponseTransfer->getQuote(),
            $quoteApprovalTransfer,
        );

        return $this->createSuccessfulQuoteApprovalResponseTransfer($quoteApprovalTransfer)
            ->setQuote($quoteTransfer);
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\MessageTransfer> $messageTransfers
     *
     * @return \Generated\Shared\Transfer\QuoteApprovalResponseTransfer
     */
    protected function createNotSuccessfulQuoteApprovalResponseTransfer(ArrayObject $messageTransfers): QuoteApprovalResponseTransfer
    {
        return (new QuoteApprovalResponseTransfer())
            ->setMessages($messageTransfers)
            ->setIsSuccessful(false);
    }

    protected function replaceQuoteApprovalInQuote(QuoteTransfer $quoteTransfer, QuoteApprovalTransfer $updatedQuoteApprovalTransfer): QuoteTransfer
    {
        foreach ($quoteTransfer->getQuoteApprovals() as $key => $quoteApprovalTransfer) {
            if ($quoteApprovalTransfer->getIdQuoteApproval() === $updatedQuoteApprovalTransfer->getIdQuoteApproval()) {
                $quoteTransfer->getQuoteApprovals()->offsetSet($key, $updatedQuoteApprovalTransfer);

                break;
            }
        }

        return $quoteTransfer;
    }

    protected function updateQuoteApprovalWithStatus(QuoteApprovalTransfer $quoteApprovalTransfer, string $status): QuoteApprovalTransfer
    {
        $this->quoteApprovalEntityManager->updateQuoteApprovalWithStatus(
            $quoteApprovalTransfer->getIdQuoteApproval(),
            $status,
        );

        return $quoteApprovalTransfer->setStatus($status);
    }

    protected function createSuccessfulQuoteApprovalResponseTransfer(QuoteApprovalTransfer $quoteApprovalTransfer): QuoteApprovalResponseTransfer
    {
        return (new QuoteApprovalResponseTransfer())
            ->setIsSuccessful(true)
            ->setQuoteApproval($quoteApprovalTransfer)
            ->addMessage($this->quoteApprovalMessageBuilder->getSuccessMessage($quoteApprovalTransfer, $quoteApprovalTransfer->getStatus()));
    }
}
