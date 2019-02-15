<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteApproval\Business\QuoteApproval;

use ArrayObject;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Generated\Shared\Transfer\QuoteApprovalCreateRequestTransfer;
use Generated\Shared\Transfer\QuoteApprovalResponseTransfer;
use Generated\Shared\Transfer\QuoteApprovalTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Shared\QuoteApproval\QuoteApprovalConfig;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\QuoteApproval\Business\Quote\QuoteLockerInterface;
use Spryker\Zed\QuoteApproval\Dependency\Facade\QuoteApprovalToSharedCartFacadeInterface;
use Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalEntityManagerInterface;

class QuoteApprovalCreator implements QuoteApprovalCreatorInterface
{
    use TransactionTrait;

    /**
     * @uses SharedCartConfig::PERMISSION_GROUP_READ_ONLY
     */
    protected const PERMISSION_GROUP_READ_ONLY = 'READ_ONLY';
    protected const GLOSSARY_KEY_APPROVAL_CREATED = 'quote_approval.created';

    /**
     * @var \Spryker\Zed\QuoteApproval\Business\Quote\QuoteLockerInterface
     */
    protected $quoteLocker;

    /**
     * @var \Spryker\Zed\QuoteApproval\Business\QuoteApproval\QuoteApprovalRequestValidatorInterface
     */
    protected $quoteApprovalRequestValidator;

    /**
     * @var \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalEntityManagerInterface
     */
    protected $quoteApprovalEntityManager;

    /**
     * @var \Spryker\Zed\QuoteApproval\Dependency\Facade\QuoteApprovalToSharedCartFacadeInterface
     */
    protected $sharedCartFacade;

    /**
     * @param \Spryker\Zed\QuoteApproval\Business\Quote\QuoteLockerInterface $quoteLocker
     * @param \Spryker\Zed\QuoteApproval\Dependency\Facade\QuoteApprovalToSharedCartFacadeInterface $sharedCartFacade
     * @param \Spryker\Zed\QuoteApproval\Business\QuoteApproval\QuoteApprovalRequestValidatorInterface $quoteApprovalRequestValidator
     * @param \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalEntityManagerInterface $quoteApprovalEntityManager
     */
    public function __construct(
        QuoteLockerInterface $quoteLocker,
        QuoteApprovalToSharedCartFacadeInterface $sharedCartFacade,
        QuoteApprovalRequestValidatorInterface $quoteApprovalRequestValidator,
        QuoteApprovalEntityManagerInterface $quoteApprovalEntityManager
    ) {
        $this->quoteLocker = $quoteLocker;
        $this->sharedCartFacade = $sharedCartFacade;
        $this->quoteApprovalRequestValidator = $quoteApprovalRequestValidator;
        $this->quoteApprovalEntityManager = $quoteApprovalEntityManager;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteApprovalCreateRequestTransfer $quoteApprovalCreateRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteApprovalResponseTransfer
     */
    public function createQuoteApproval(QuoteApprovalCreateRequestTransfer $quoteApprovalCreateRequestTransfer): QuoteApprovalResponseTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(function () use ($quoteApprovalCreateRequestTransfer) {
            return $this->executeCreateQuoteApprovalTransaction($quoteApprovalCreateRequestTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteApprovalCreateRequestTransfer $quoteApprovalCreateRequestTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteApprovalResponseTransfer
     */
    protected function executeCreateQuoteApprovalTransaction(QuoteApprovalCreateRequestTransfer $quoteApprovalCreateRequestTransfer): QuoteApprovalResponseTransfer
    {
        $quoteApprovalRequestValidationResponseTransfer = $this->quoteApprovalRequestValidator
            ->validateQuoteApprovalCreateRequest($quoteApprovalCreateRequestTransfer);

        if (!$quoteApprovalRequestValidationResponseTransfer->getIsSuccessful()) {
            return $this->createNotSuccessfulQuoteApprovalResponseTransfer(
                $quoteApprovalRequestValidationResponseTransfer->getMessages()
            );
        }

        $quoteTransfer = $quoteApprovalRequestValidationResponseTransfer->getQuote();
        $quoteApprovalTransfer = $this->createQuoteApprovalTransfer(
            $quoteTransfer->getIdQuote(),
            $quoteApprovalCreateRequestTransfer->getApproverCompanyUserId()
        );

        $this->quoteLocker->lockQuote($quoteTransfer);
        $this->sharedCartFacade->deleteShareForQuote($quoteTransfer);

        if (!$this->isQuoteOwner($quoteTransfer, $quoteApprovalTransfer->getApprover())) {
            $this->shareQuoteToApprover($quoteApprovalTransfer);
        }

        return $this->createSuccessfullQuoteApprovalResponseTransfer($quoteApprovalTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteApprovalTransfer $quoteApprovalTransfer
     *
     * @return void
     */
    protected function shareQuoteToApprover(QuoteApprovalTransfer $quoteApprovalTransfer): void
    {
        $this->sharedCartFacade->shareQuoteWithCompanyUser(
            $quoteApprovalTransfer->getFkQuote(),
            $quoteApprovalTransfer->getApproverCompanyUserId(),
            static::PERMISSION_GROUP_READ_ONLY
        );
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CompanyUserTransfer $companyUserTransfer
     *
     * @return bool
     */
    protected function isQuoteOwner(QuoteTransfer $quoteTransfer, CompanyUserTransfer $companyUserTransfer): bool
    {
        $quoteApproverCustomerReference = $companyUserTransfer->getCustomer()->getCustomerReference();

        return $quoteTransfer->getCustomerReference() === $quoteApproverCustomerReference;
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\MessageTransfer[] $messageTransfers
     *
     * @return \Generated\Shared\Transfer\QuoteApprovalResponseTransfer
     */
    protected function createNotSuccessfulQuoteApprovalResponseTransfer(ArrayObject $messageTransfers): QuoteApprovalResponseTransfer
    {
        $quoteApprovalResponseTransfer = new QuoteApprovalResponseTransfer();

        $quoteApprovalResponseTransfer->setIsSuccessful(false);
        $quoteApprovalResponseTransfer->setMessages($messageTransfers);

        return $quoteApprovalResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteApprovalTransfer $quoteApprovalTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteApprovalResponseTransfer
     */
    protected function createSuccessfullQuoteApprovalResponseTransfer(QuoteApprovalTransfer $quoteApprovalTransfer): QuoteApprovalResponseTransfer
    {
        $approverCustomerTransfer = $quoteApprovalTransfer->getApprover()->getCustomer();

        $quoteApprovalResponseTransfer = new QuoteApprovalResponseTransfer();
        $quoteApprovalResponseTransfer->setIsSuccessful(true);
        $quoteApprovalResponseTransfer->addMessage(
            $this->createMessageTransfer(
                static::GLOSSARY_KEY_APPROVAL_CREATED,
                [
                    '%first_name%' => $approverCustomerTransfer->getFirstName(),
                    '%last_name%' => $approverCustomerTransfer->getLastName(),
                ]
            )
        );

        return $quoteApprovalResponseTransfer;
    }

    /**
     * @param string $message
     * @param array $parameters
     *
     * @return \Generated\Shared\Transfer\MessageTransfer
     */
    protected function createMessageTransfer(string $message, array $parameters = []): MessageTransfer
    {
        $messageTransfer = new MessageTransfer();
        $messageTransfer->setValue($message);
        $messageTransfer->setParameters($parameters);

        return $messageTransfer;
    }

    /**
     * @param int $idQuote
     * @param int $idCompanyUser
     *
     * @return \Generated\Shared\Transfer\QuoteApprovalTransfer
     */
    protected function createQuoteApprovalTransfer(int $idQuote, int $idCompanyUser): QuoteApprovalTransfer
    {
        $quoteApprovalTransfer = new QuoteApprovalTransfer();

        $quoteApprovalTransfer->setStatus(QuoteApprovalConfig::STATUS_WAITING);
        $quoteApprovalTransfer->setApproverCompanyUserId($idCompanyUser);
        $quoteApprovalTransfer->setFkQuote($idQuote);

        return $this->quoteApprovalEntityManager
            ->saveQuoteApproval($quoteApprovalTransfer);
    }
}
