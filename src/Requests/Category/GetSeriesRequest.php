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
 * GET /series/cat/{category}/producer/{producer}/{filters}
 */
class GetSeriesRequest extends CategoryRequest
{
    /**
     * @var string
     */
    protected string $producer;

    /**
     * @param CategoryType|string $category
     * @param int|string $producer Mandatory producer ID. In categories where the
     *                             producer is the country, pass the country ID.
     * @param Filters|null $filters
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the category or producer is malformed.
     */
    public function __construct(CategoryType|string $category, int|string $producer, ?Filters $filters = null)
    {
        $this->producer = Guard::printable((string) $producer, 'Producer');

        parent::__construct($category, $filters);
    }

    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'series';
    }

    /**
     * Mandatory path segments placed between the category and the filters.
     *
     * @return string
     */
    protected function pathSuffix(): string
    {
        return '/producer/' . rawurlencode($this->producer);
    }
}
