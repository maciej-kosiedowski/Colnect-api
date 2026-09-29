<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * Whether the producer of the category is the issuing country. When true there is no producers list and the country filter must be named producer.
 *
 * GET /producer_is_country/cat/{category}
 */
class GetProducerIsCountryRequest extends CategoryRequest
{
    protected function action(): string
    {
        return 'producer_is_country';
    }
}
