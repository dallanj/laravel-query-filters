<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class Company extends Model
{
    public $timestamps = false;

    protected $guarded = [];
}
