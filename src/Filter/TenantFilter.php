<?php

namespace App\Filter;

use App\Entity\TenantInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class TenantFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if (!$targetEntity->getReflectionClass()->implementsInterface(TenantInterface::class)) {
            return '';
        }

        if (!$targetEntity->hasAssociation('company')) {
            return '';
        }

        $columnName = $targetEntity->getSingleAssociationJoinColumnName('company');

        return sprintf(
            '%s.%s = %s',
            $targetTableAlias,
            $columnName,
            $this->getParameter('current_company_id')
        );
    }
}
