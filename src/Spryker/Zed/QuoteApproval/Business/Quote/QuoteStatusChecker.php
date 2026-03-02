<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteApproval\Business\Quote;

use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Shared\QuoteApproval\QuoteApprovalConfig;
use Spryker\Zed\Kernel\PermissionAwareTrait;
use Spryker\Zed\QuoteApproval\Business\Permission\ContextProvider\PermissionContextProviderInterface;
use Spryker\Zed\QuoteApproval\Communication\Plugin\Permission\PlaceOrderPermissionPlugin;

class QuoteStatusChecker implements QuoteStatusCheckerInterface
{
    use PermissionAwareTrait;

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_CART_REQUIRE_APPROVAL = 'quote_approval.cart.require_approval';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_CART_WAITING_APPROVAL = 'quote_approval.cart.waiting_approval';

    /**
     * @var \Spryker\Zed\QuoteApproval\Business\Quote\QuoteStatusCalculatorInterface
     */
    protected $quoteStatusCalculator;

    /**
     * @var \Spryker\Zed\QuoteApproval\Business\Permission\ContextProvider\PermissionContextProviderInterface
     */
    protected $permissionContextProvider;

    public function __construct(
        QuoteStatusCalculatorInterface $quoteStatusCalculator,
        PermissionContextProviderInterface $permissionContextProvider
    ) {
        $this->quoteStatusCalculator = $quoteStatusCalculator;
        $this->permissionContextProvider = $permissionContextProvider;
    }

    /**
     * @see \Spryker\Client\QuoteApproval\Quote\QuoteStatusChecker::isQuoteInApprovalProcess()
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    public function isQuoteInApprovalProcess(QuoteTransfer $quoteTransfer): bool
    {
        return in_array($this->quoteStatusCalculator->calculateQuoteStatus($quoteTransfer), [
            QuoteApprovalConfig::STATUS_WAITING,
            QuoteApprovalConfig::STATUS_APPROVED,
        ], true);
    }

    /**
     * @see \Spryker\Client\QuoteApproval\Quote\QuoteStatusChecker::isQuoteWaitingForApproval()
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    public function isQuoteWaitingForApproval(QuoteTransfer $quoteTransfer): bool
    {
        $quoteStatus = $this->quoteStatusCalculator
            ->calculateQuoteStatus($quoteTransfer);

        return $quoteStatus === QuoteApprovalConfig::STATUS_WAITING;
    }

    public function isQuoteReadyForCheckout(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): CheckoutResponseTransfer
    {
        $quoteStatus = $this->quoteStatusCalculator
            ->calculateQuoteStatus($quoteTransfer);

        if (!$quoteTransfer->getCustomer() || !$quoteTransfer->getCustomer()->getCompanyUserTransfer()) {
            return $checkoutResponseTransfer;
        }

        if ($quoteStatus === QuoteApprovalConfig::STATUS_WAITING) {
            return $this->addCheckoutError($checkoutResponseTransfer, static::GLOSSARY_KEY_CART_WAITING_APPROVAL);
        }

        if ($this->isQuoteApprovalRequired($quoteTransfer, $quoteStatus)) {
            return $this->addCheckoutError($checkoutResponseTransfer, static::GLOSSARY_KEY_CART_REQUIRE_APPROVAL);
        }

        return $checkoutResponseTransfer;
    }

    protected function isQuoteApprovalRequired(QuoteTransfer $quoteTransfer, ?string $quoteStatus): bool
    {
        $idCompanyUser = $quoteTransfer->getCustomer()
                ->getCompanyUserTransfer()
                ->requireIdCompanyUser()
                ->getIdCompanyUser();

        if ($this->can(PlaceOrderPermissionPlugin::KEY, $idCompanyUser, $this->permissionContextProvider->provideContext($quoteTransfer))) {
            return false;
        }

        return $quoteStatus !== QuoteApprovalConfig::STATUS_APPROVED;
    }

    protected function addCheckoutError(CheckoutResponseTransfer $checkoutResponseTransfer, string $message): CheckoutResponseTransfer
    {
        $checkoutResponseTransfer->setIsSuccess(false)
            ->addError(
                (new CheckoutErrorTransfer())->setMessage($message),
            );

        return $checkoutResponseTransfer;
    }
}
