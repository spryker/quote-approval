<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\QuoteApproval;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\QuoteApprovalTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\QuoteApproval\StatusCalculator\QuoteApprovalStatusCalculator;
use Spryker\Client\QuoteApproval\StatusCalculator\QuoteApprovalStatusCalculatorInterface;
use Spryker\Shared\QuoteApproval\QuoteApprovalConfig;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Shared
 * @group QuoteApproval
 * @group QuoteApprovalStatusCalculatorTest
 * Add your own group annotations below this line
 */
class QuoteApprovalStatusCalculatorTest extends Unit
{
    /**
     * @return void
     */
    public function testCalculateQuoteStatusWithOneWaitingShouldReturnWaiting(): void
    {
        $statuses = [
            QuoteApprovalConfig::STATUS_WAITING,
        ];

        $quoteTransfer = $this->createQuoteTransfer($statuses);
        $result = $this->createQuoteApprovalStatusCalculator()->calculateQuoteStatus($quoteTransfer);

        $this->assertSame($result, QuoteApprovalConfig::STATUS_WAITING);
    }

    /**
     * @return void
     */
    public function testCalculateQuoteStatusWithApprovedShouldReturnApproved(): void
    {
        $statuses = [
            QuoteApprovalConfig::STATUS_APPROVED,
        ];

        $quoteTransfer = $this->createQuoteTransfer($statuses);
        $result = $this->createQuoteApprovalStatusCalculator()->calculateQuoteStatus($quoteTransfer);

        $this->assertSame($result, QuoteApprovalConfig::STATUS_APPROVED);
    }

    /**
     * @return void
     */
    public function testCalculateQuoteStatusWithDeclinedShouldReturnDeclined(): void
    {
        $statuses = [
            QuoteApprovalConfig::STATUS_DECLINED,
        ];

        $quoteTransfer = $this->createQuoteTransfer($statuses);
        $result = $this->createQuoteApprovalStatusCalculator()->calculateQuoteStatus($quoteTransfer);

        $this->assertSame($result, QuoteApprovalConfig::STATUS_DECLINED);
    }

    /**
     * @return void
     */
    public function testCalculateQuoteStatusWithWaitingAndApprovedShouldReturnApproved(): void
    {
        $statuses = [
            QuoteApprovalConfig::STATUS_WAITING,
            QuoteApprovalConfig::STATUS_APPROVED,
        ];

        $quoteTransfer = $this->createQuoteTransfer($statuses);
        $result = $this->createQuoteApprovalStatusCalculator()->calculateQuoteStatus($quoteTransfer);

        $this->assertSame($result, QuoteApprovalConfig::STATUS_APPROVED);
    }

    /**
     * @return void
     */
    public function testCalculateQuoteStatusWithWaitingAndDeclinedShouldReturnWaiting(): void
    {
        $statuses = [
            QuoteApprovalConfig::STATUS_WAITING,
            QuoteApprovalConfig::STATUS_DECLINED,
        ];

        $quoteTransfer = $this->createQuoteTransfer($statuses);
        $result = $this->createQuoteApprovalStatusCalculator()->calculateQuoteStatus($quoteTransfer);

        $this->assertSame($result, QuoteApprovalConfig::STATUS_WAITING);
    }

    /**
     * @return void
     */
    public function testCalculateQuoteStatusWithWaitingDeclinedAndApprovedShouldReturnApproved(): void
    {
        $statuses = [
            QuoteApprovalConfig::STATUS_WAITING,
            QuoteApprovalConfig::STATUS_DECLINED,
            QuoteApprovalConfig::STATUS_APPROVED,
        ];

        $quoteTransfer = $this->createQuoteTransfer($statuses);
        $result = $this->createQuoteApprovalStatusCalculator()->calculateQuoteStatus($quoteTransfer);

        $this->assertSame($result, QuoteApprovalConfig::STATUS_APPROVED);
    }

    /**
     * @return void
     */
    public function testCalculateQuoteStatusWithEmptyDataShouldReturnNull(): void
    {
        $statuses = [];

        $quoteTransfer = $this->createQuoteTransfer($statuses);
        $result = $this->createQuoteApprovalStatusCalculator()->calculateQuoteStatus($quoteTransfer);

        $this->assertNull($result);
    }

    /**
     * @param string[] $statuses
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransfer(array $statuses): QuoteTransfer
    {
        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->setApprovals($this->createQuoteApprovalTransfers($statuses));

        return $quoteTransfer;
    }

    /**
     * @param string[] $statuses
     *
     * @return \ArrayObject|\Generated\Shared\Transfer\QuoteApprovalTransfer[]
     */
    protected function createQuoteApprovalTransfers(array $statuses): ArrayObject
    {
        $quoteApprovalTransfers = [];

        foreach ($statuses as $status) {
            $quoteApprovalTransfers[] = (new QuoteApprovalTransfer())->setStatus($status);
        }

        return new ArrayObject($quoteApprovalTransfers);
    }

    /**
     * @return \Spryker\Client\QuoteApproval\StatusCalculator\QuoteApprovalStatusCalculatorInterface
     */
    protected function createQuoteApprovalStatusCalculator(): QuoteApprovalStatusCalculatorInterface
    {
        return new QuoteApprovalStatusCalculator();
    }
}
