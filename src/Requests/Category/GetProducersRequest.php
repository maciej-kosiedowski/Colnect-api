<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Requests\CategoryRequest;
use Slimad\ColnectApi\Support\Filters;
use Slimad\ColnectApi\Support\Guard;

/**
 * List of producers of a country. The country filter is mandatory.
 *
 * GET /producers/cat/{category}/country/{country}/{filters}
 *
 * Returns [producer_id, producer_name, item_count]. Categories whose producer
 * is the country itself have no producers list, see
 * GetProducerIsCountryRequest and CategoryType::producerIsCountry().
 */
class GetProducersRequest extends CategoryRequest
{
    /**
     * @var string
     */
    protected string $country;

    /**
     * @param CategoryType|string $category
     * @param int|string $country Mandatory country ID.
     * @param Filters|null $filters
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the category or country is malformed.
     */
    public function __construct(CategoryType|string $category, int|string $country, ?Filters $filters = null)
    {
        $this->country = Guard::printable((string) $country, 'Country');

        parent::__construct($category, $filters);
    }

    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'producers';
    }

    /**
     * Mandatory path segments placed between the category and the filters.
     *
     * @return string
     */
    protected function pathSuffix(): string
    {
        return '/country/' . rawurlencode($this->country);
    }
}
