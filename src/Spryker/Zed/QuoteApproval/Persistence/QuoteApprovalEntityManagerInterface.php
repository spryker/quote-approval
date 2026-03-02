<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteApproval\Persistence;

use Generated\Shared\Transfer\QuoteApprovalTransfer;

interface QuoteApprovalEntityManagerInterface
{
    public function createQuoteApproval(QuoteApprovalTransfer $quoteApprovalTransfer): QuoteApprovalTransfer;

    public function updateQuoteApprovalWithStatus(int $idQuoteApproval, string $status): void;

    public function deleteQuoteApprovalById(int $idQuoteApproval): void;

    public function removeApprovalsByIdQuote(int $idQuote): void;
}
