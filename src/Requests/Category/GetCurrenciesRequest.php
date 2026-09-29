<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of currencies. Filters are optional; returns [currency_id, currency_name, item_count].
 *
 * GET /currencies/cat/{category}
 */
class GetCurrenciesRequest extends CategoryRequest
{
    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'currencies';
    }
}
