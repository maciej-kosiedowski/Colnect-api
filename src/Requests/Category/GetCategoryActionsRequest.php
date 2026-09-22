<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of the actions available in the category; returns an array of action names.
 *
 * GET /category_actions/cat/{category}
 */
class GetCategoryActionsRequest extends CategoryRequest
{
    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'category_actions';
    }
}
