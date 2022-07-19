<?php

declare(strict_types=1);

namespace App\ORM\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class ArchiveFilter extends SQLFilter
{
    /**
     * @param string $targetTableAlias
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->hasField('archivedAt')) {
            return $targetTableAlias.'.archived_at IS NULL';
        }

        return '';
    }
}
