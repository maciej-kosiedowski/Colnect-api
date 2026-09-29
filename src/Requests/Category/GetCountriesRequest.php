<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of countries, with the number of items each of them has. Filters are optional; returns [country_id, country_name, item_count].
 *
 * GET /countries/cat/{category}
 */
class GetCountriesRequest extends CategoryRequest
{
    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'countries';
    }
}
