<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of items and their info. Filters are mandatory.
 *
 * GET /list/cat/{category}/{filters}
 *
 * Returns [item_id, series_id, producer_id, front_picture_id, back_picture_id,
 * item_description, catalog_codes, item_name]. Use Filters::sort() to change
 * the ordering, see GetCategorySortOptionsRequest for the available names.
 */
class GetItemsListRequest extends CategoryRequest
{
    protected function action(): string
    {
        return 'list';
    }

    protected function requiresFilters(): bool
    {
        return true;
    }
}
