<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Requests\CategoryRequest;
use Slimad\ColnectApi\Support\Filters;
use Slimad\ColnectApi\Support\Guard;

/**
 * List of series of a producer. The producer filter is mandatory; returns [series_id, series_name, item_count].
 *
 * GET /series/cat/{category}/{country|producer}/{producer}/{filters}
 */
class GetSeriesRequest extends CategoryRequest
{
    protected string $producer;

    /**
     * @param int|string $producer Mandatory producer ID. In categories where the
     *                             producer is the country, pass the country ID.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the category or producer is malformed.
     */
    public function __construct(CategoryType|string $category, int|string $producer, ?Filters $filters = null)
    {
        $this->producer = Guard::printable((string) $producer, 'Producer');

        parent::__construct($category, $filters);
    }

    protected function action(): string
    {
        return 'series';
    }

    protected function pathSuffix(): string
    {
        // CAPI rejects `producer/` with HTTP 400 "Invalid filter requested" where the producer is
        // the country (verified for stamps); those categories take the `country/` filter.
        $filter = CategoryType::tryFrom($this->category)?->producerIsCountry() === true ? 'country' : 'producer';

        return '/' . $filter . '/' . rawurlencode($this->producer);
    }
}
