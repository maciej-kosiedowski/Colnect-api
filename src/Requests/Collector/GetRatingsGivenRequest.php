<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Collector;

use Slimad\ColnectApi\Requests\CollectorRequest;

/**
 * Ratings given to other members; returns [collector, rating, date, comment].
 *
 * GET /ratings_given/collector/{collector}
 */
class GetRatingsGivenRequest extends CollectorRequest
{
    protected function action(): string
    {
        return 'ratings_given';
    }
}
