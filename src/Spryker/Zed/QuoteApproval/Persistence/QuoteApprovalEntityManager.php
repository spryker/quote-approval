<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spryker\Zed\QuoteApproval\Persistence;

use Generated\Shared\Transfer\QuoteApprovalTransfer;
use Generated\Shared\Transfer\SpyQuoteApprovalEntityTransfer;
use Orm\Zed\QuoteApproval\Persistence\SpyQuoteApproval;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalPersistenceFactory getFactory()
 */
class QuoteApprovalEntityManager extends AbstractEntityManager implements QuoteApprovalEntityManagerInterface
{
    public function createQuoteApproval(QuoteApprovalTransfer $quoteApprovalTransfer): QuoteApprovalTransfer
    {
        $quoteApprovalEntity = $this->getFactory()
            ->createQuoteApprovalMapper()
            ->mapQuoteApprovalTransferToEntity(
                $quoteApprovalTransfer,
                new SpyQuoteApproval(),
            );

        $quoteApprovalEntity->save();

        return $this->getFactory()
            ->createQuoteApprovalMapper()
            ->mapQuoteApprovalEntityToTransfer($quoteApprovalEntity, $quoteApprovalTransfer);
    }

    public function updateQuoteApprovalWithStatus(int $idQuoteApproval, string $status): void
    {
        $quoteApprovalEntity = $this->getFactory()
            ->createQuoteApprovalPropelQuery()
            ->filterByIdQuoteApproval($idQuoteApproval)
            ->findOne();

        if (!$quoteApprovalEntity) {
            return;
        }

        $quoteApprovalEntity->setStatus($status)
            ->save();
    }

    public function deleteQuoteApprovalById(int $idQuoteApproval): void
    {
        $this->getFactory()
            ->createQuoteApprovalPropelQuery()
            ->filterByIdQuoteApproval($idQuoteApproval)
            ->findOne()
            ->delete();
    }

    public function removeApprovalsByIdQuote(int $idQuote): void
    {
        $quoteApprovalEntities = $this->getFactory()
            ->createQuoteApprovalPropelQuery()
            ->filterByFkQuote($idQuote)
            ->find();

        foreach ($quoteApprovalEntities as $quoteApprovalEntity) {
            $quoteApprovalEntity->delete();
        }
    }
}
