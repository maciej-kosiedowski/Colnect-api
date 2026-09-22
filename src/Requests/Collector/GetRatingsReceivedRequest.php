<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Collector;

use Slimad\ColnectApi\Requests\CollectorRequest;

/**
 * Ratings received from other members; returns [collector, rating, date, comment].
 *
 * GET /ratings_received/collector/{collector}
 */
class GetRatingsReceivedRequest extends CollectorRequest
{
    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'ratings_received';
    }
}
