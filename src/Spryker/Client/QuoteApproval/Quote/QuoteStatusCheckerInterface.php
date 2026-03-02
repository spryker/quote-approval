<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\QuoteApproval\Quote;

use Generated\Shared\Transfer\QuoteTransfer;

interface QuoteStatusCheckerInterface
{
    public function isQuoteApprovalRequired(QuoteTransfer $quoteTransfer): bool;

    public function canQuoteBeApprovedByCurrentCustomer(QuoteTransfer $quoteTransfer): bool;

    public function isQuoteWaitingForApproval(QuoteTransfer $quoteTransfer): bool;

    public function isQuoteApproved(QuoteTransfer $quoteTransfer): bool;

    public function isQuoteDeclined(QuoteTransfer $quoteTransfer): bool;

    public function isQuoteInApprovalProcess(QuoteTransfer $quoteTransfer): bool;
}
