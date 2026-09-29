<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of item IDs only, to be combined with GetItemRequest. Filters are mandatory; returns an array of item_id.
 *
 * GET /list_id/cat/{category}
 */
class GetItemIdsRequest extends CategoryRequest
{
    protected function action(): string
    {
        return 'list_id';
    }

    protected function requiresFilters(): bool
    {
        return true;
    }
}
