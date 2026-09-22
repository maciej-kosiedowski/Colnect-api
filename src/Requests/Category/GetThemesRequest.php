<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of themes. Filters are optional; returns [theme_id, theme_name, item_count].
 *
 * GET /themes/cat/{category}
 */
class GetThemesRequest extends CategoryRequest
{
    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'themes';
    }
}
