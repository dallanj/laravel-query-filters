<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Tests\Fixtures;

use Dallanj\QueryFilters\Concerns\HasSearchableFields;
use Dallanj\QueryFilters\Contracts\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Record extends Model implements Searchable
{
    use HasSearchableFields;

    public $timestamps = false;

    protected $guarded = [];

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function searchableFields(): array
    {
        return [
            $this->searchColumn('title'),
            $this->searchRelation('company', 'name'),
        ];
    }
}
