<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Contracts;

interface Searchable
{
    /** @return list<SearchTarget> */
    public function searchableFields(): array;
}
