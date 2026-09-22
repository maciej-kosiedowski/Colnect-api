<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of the sort options available in the category; returns [sort_name => sort_title].
 *
 * GET /category_sort_options/cat/{category}
 */
class GetCategorySortOptionsRequest extends CategoryRequest
{
    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'category_sort_options';
    }
}
