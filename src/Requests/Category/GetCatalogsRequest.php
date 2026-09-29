<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of catalogs. Filters are optional; returns [catalog_id, catalog_name, item_count].
 *
 * GET /catalogs/cat/{category}
 */
class GetCatalogsRequest extends CategoryRequest
{
    protected function action(): string
    {
        return 'catalogs';
    }
}
