<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteApproval\Business\QuoteApproval;

use Generated\Shared\Transfer\CompanyUserCollectionTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface QuoteApproverListProviderInterface
{
    public function getApproversList(QuoteTransfer $quoteTransfer): CompanyUserCollectionTransfer;
}
