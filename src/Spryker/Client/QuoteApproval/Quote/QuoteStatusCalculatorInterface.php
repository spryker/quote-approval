<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\QuoteApproval\Quote;

use Generated\Shared\Transfer\QuoteTransfer;

interface QuoteStatusCalculatorInterface
{
    public function calculateQuoteStatus(QuoteTransfer $quoteTransfer): ?string;
}
