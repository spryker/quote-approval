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
    /**
     * @param \Generated\Shared\Transfer\QuoteApprovalTransfer $quoteApprovalTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteApprovalTransfer
     */
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

    /**
     * @param int $idQuoteApproval
     * @param string $status
     *
     * @return void
     */
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

    /**
     * @param int $idQuoteApproval
     *
     * @return void
     */
    public function deleteQuoteApprovalById(int $idQuoteApproval): void
    {
        $this->getFactory()
            ->createQuoteApprovalPropelQuery()
            ->filterByIdQuoteApproval($idQuoteApproval)
            ->findOne()
            ->delete();
    }

    /**
     * @param int $idQuote
     *
     * @return void
     */
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
