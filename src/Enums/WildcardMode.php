<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Enums;

enum WildcardMode
{
    case Literal;
    case Pattern;
    case Prefix;
}
