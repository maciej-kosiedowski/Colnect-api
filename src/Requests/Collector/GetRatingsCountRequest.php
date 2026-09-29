<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Collector;

use Slimad\ColnectApi\Requests\CollectorRequest;

/**
 * How many ratings of each type the collector received; returns [negative_ratings, neutral_ratings, positive_ratings].
 *
 * GET /ratings_count/collector/{collector}
 */
class GetRatingsCountRequest extends CollectorRequest
{
    protected function action(): string
    {
        return 'ratings_count';
    }
}
