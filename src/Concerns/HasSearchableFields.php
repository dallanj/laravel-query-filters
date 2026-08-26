<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Concerns;

use Dallanj\QueryFilters\Search\Column;
use Dallanj\QueryFilters\Search\Relation;

trait HasSearchableFields
{
    protected function searchColumn(string $column): Column
    {
        return new Column($column);
    }

    protected function searchRelation(string $relation, string $column): Relation
    {
        return new Relation($relation, $column);
    }
}
