<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteApproval\Persistence;

use Orm\Zed\QuoteApproval\Persistence\SpyQuoteApprovalQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\QuoteApproval\Persistence\Mapper\QuoteApprovalMapper;
use Spryker\Zed\QuoteApproval\Persistence\Mapper\QuoteApprovalMapperInterface;

/**
 * @method \Spryker\Zed\QuoteApproval\QuoteApprovalConfig getConfig()
 * @method \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\QuoteApproval\Persistence\QuoteApprovalRepositoryInterface getRepository()
 */
class QuoteApprovalPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\QuoteApproval\Persistence\SpyQuoteApprovalQuery
     */
    public function createSpyQuoteApprovalQuery(): SpyQuoteApprovalQuery
    {
        return SpyQuoteApprovalQuery::create();
    }

    /**
     * @return \Spryker\Zed\QuoteApproval\Persistence\Mapper\QuoteApprovalMapperInterface
     */
    public function createQuoteApprovalMapper(): QuoteApprovalMapperInterface
    {
        return new QuoteApprovalMapper();
    }
}