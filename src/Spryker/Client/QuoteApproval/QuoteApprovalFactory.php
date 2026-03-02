<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\QuoteApproval;

use Spryker\Client\Kernel\AbstractFactory;
use Spryker\Client\QuoteApproval\Checker\QuoteChecker;
use Spryker\Client\QuoteApproval\Checker\QuoteCheckerInterface;
use Spryker\Client\QuoteApproval\Dependency\Client\QuoteApprovalToQuoteClientInterface;
use Spryker\Client\QuoteApproval\Dependency\Client\QuoteApprovalToZedRequestClientInterface;
use Spryker\Client\QuoteApproval\Permission\ContextProvider\PermissionContextProvider;
use Spryker\Client\QuoteApproval\Permission\ContextProvider\PermissionContextProviderInterface;
use Spryker\Client\QuoteApproval\Permission\PermissionLimitCalculator;
use Spryker\Client\QuoteApproval\Permission\PermissionLimitCalculatorInterface;
use Spryker\Client\QuoteApproval\Quote\QuoteStatusCalculator;
use Spryker\Client\QuoteApproval\Quote\QuoteStatusCalculatorInterface;
use Spryker\Client\QuoteApproval\Quote\QuoteStatusChecker;
use Spryker\Client\QuoteApproval\Quote\QuoteStatusCheckerInterface;
use Spryker\Client\QuoteApproval\QuoteApproval\QuoteApprovalCreator;
use Spryker\Client\QuoteApproval\QuoteApproval\QuoteApprovalCreatorInterface;
use Spryker\Client\QuoteApproval\QuoteApproval\QuoteApprovalReader;
use Spryker\Client\QuoteApproval\QuoteApproval\QuoteApprovalReaderInterface;
use Spryker\Client\QuoteApproval\Zed\QuoteApprovalStub;
use Spryker\Client\QuoteApproval\Zed\QuoteApprovalStubInterface;

/**
 * @method \Spryker\Client\QuoteApproval\QuoteApprovalConfig getConfig()
 */
class QuoteApprovalFactory extends AbstractFactory
{
    public function createQuoteStatusCalculator(): QuoteStatusCalculatorInterface
    {
        return new QuoteStatusCalculator();
    }

    public function createQuoteStatusChecker(): QuoteStatusCheckerInterface
    {
        return new QuoteStatusChecker(
            $this->createQuoteStatusCalculator(),
            $this->createPermissionContextProvider(),
        );
    }

    public function createQuoteChecker(): QuoteCheckerInterface
    {
        return new QuoteChecker(
            $this->getConfig(),
            $this->getQuoteApplicableForApprovalCheckPlugins(),
        );
    }

    public function createPermissionContextProvider(): PermissionContextProviderInterface
    {
        return new PermissionContextProvider($this->getConfig());
    }

    public function createQuoteApprovalReader(): QuoteApprovalReaderInterface
    {
        return new QuoteApprovalReader();
    }

    public function createPermissionLimitCalculator(): PermissionLimitCalculatorInterface
    {
        return new PermissionLimitCalculator();
    }

    public function createQuoteApprovalStub(): QuoteApprovalStubInterface
    {
        return new QuoteApprovalStub(
            $this->getZedRequestClient(),
        );
    }

    public function createQuoteApprovalCreator(): QuoteApprovalCreatorInterface
    {
        return new QuoteApprovalCreator(
            $this->createQuoteApprovalStub(),
            $this->createQuoteChecker(),
        );
    }

    public function getZedRequestClient(): QuoteApprovalToZedRequestClientInterface
    {
        return $this->getProvidedDependency(QuoteApprovalDependencyProvider::CLIENT_ZED_REQUEST);
    }

    public function getQuoteClient(): QuoteApprovalToQuoteClientInterface
    {
        return $this->getProvidedDependency(QuoteApprovalDependencyProvider::CLIENT_QUOTE);
    }

    /**
     * @return array<\Spryker\Client\QuoteApprovalExtension\Dependency\Plugin\QuoteApplicableForApprovalCheckPluginInterface>
     */
    public function getQuoteApplicableForApprovalCheckPlugins(): array
    {
        return $this->getProvidedDependency(QuoteApprovalDependencyProvider::PLUGINS_QUOTE_APPLICABLE_FOR_APPROVAL_CHECK);
    }
}
