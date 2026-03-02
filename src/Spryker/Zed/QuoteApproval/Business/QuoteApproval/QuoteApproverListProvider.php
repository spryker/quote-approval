<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\QuoteApproval\Business\QuoteApproval;

use ArrayObject;
use Generated\Shared\Transfer\CompanyUserCollectionTransfer;
use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\PermissionAwareTrait;
use Spryker\Zed\QuoteApproval\Communication\Plugin\Permission\ApproveQuotePermissionPlugin;
use Spryker\Zed\QuoteApproval\Dependency\Facade\QuoteApprovalToCompanyRoleFacadeInterface;
use Spryker\Zed\QuoteApproval\Dependency\Facade\QuoteApprovalToCompanyUserFacadeInterface;

class QuoteApproverListProvider implements QuoteApproverListProviderInterface
{
    use PermissionAwareTrait;

    /**
     * @var \Spryker\Zed\QuoteApproval\Dependency\Facade\QuoteApprovalToCompanyRoleFacadeInterface
     */
    protected $companyRoleFacade;

    /**
     * @var \Spryker\Zed\QuoteApproval\Dependency\Facade\QuoteApprovalToCompanyUserFacadeInterface
     */
    protected $companyUserFacade;

    public function __construct(
        QuoteApprovalToCompanyRoleFacadeInterface $companyRoleFacade,
        QuoteApprovalToCompanyUserFacadeInterface $companyUserFacade
    ) {
        $this->companyRoleFacade = $companyRoleFacade;
        $this->companyUserFacade = $companyUserFacade;
    }

    public function getApproversList(QuoteTransfer $quoteTransfer): CompanyUserCollectionTransfer
    {
        $quoteTransfer
            ->requireCustomer()
            ->getCustomer()
                ->requireCompanyUserTransfer();

        $companyUserTransfer = $quoteTransfer->getCustomer()->getCompanyUserTransfer();

        $approverIds = $this->getApproversIds($companyUserTransfer->getFkCompany());

        $companyUserCollectionTransfer = $this->getCompanyUserCollectionByIds($approverIds);
        $companyUserCollectionTransfer = $this->filterByBusinessUnit($companyUserCollectionTransfer, $companyUserTransfer->getFkCompanyBusinessUnit());

        return $companyUserCollectionTransfer;
    }

    protected function getCompanyUserCollectionByIds(array $companyUserIds): CompanyUserCollectionTransfer
    {
        if (!$companyUserIds) {
            return new CompanyUserCollectionTransfer();
        }

        $filterTransfer = new CompanyUserCriteriaFilterTransfer();
        $filterTransfer->setCompanyUserIds($companyUserIds);

        return $this->companyUserFacade->getCompanyUserCollection($filterTransfer);
    }

    protected function filterByBusinessUnit(CompanyUserCollectionTransfer $companyUserCollectionTransfer, int $idBusinessUnit): CompanyUserCollectionTransfer
    {
        $companyUsers = $companyUserCollectionTransfer->getCompanyUsers()->getArrayCopy();

        foreach ($companyUsers as $key => $companyUser) {
            if ($companyUser->getFkCompanyBusinessUnit() !== $idBusinessUnit) {
                unset($companyUsers[$key]);
            }
        }

        $companyUserCollectionTransfer->setCompanyUsers(new ArrayObject($companyUsers));

        return $companyUserCollectionTransfer;
    }

    /**
     * @param int|null $idCompany
     *
     * @return array<int>
     */
    protected function getApproversIds(?int $idCompany = null): array
    {
        return $this->companyRoleFacade->getCompanyUserIdsByPermissionKey(ApproveQuotePermissionPlugin::KEY, $idCompany);
    }
}
