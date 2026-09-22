<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Enums;

/**
 * Marketplace ratings as returned by the collector rating actions.
 */
enum Rating: int
{
    case Negative = -1;
    case Neutral = 0;
    case Positive = 1;
}
