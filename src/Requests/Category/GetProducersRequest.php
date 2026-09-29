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
    protected string $country;

    /**
     * @param int|string $country Mandatory country ID.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the category or country is malformed.
     */
    public function __construct(CategoryType|string $category, int|string $country, ?Filters $filters = null)
    {
        $this->country = Guard::printable((string) $country, 'Country');

        parent::__construct($category, $filters);
    }

    protected function action(): string
    {
        return 'producers';
    }

    protected function pathSuffix(): string
    {
        return '/country/' . rawurlencode($this->country);
    }
}
