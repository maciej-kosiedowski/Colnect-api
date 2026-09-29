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
    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'list_id';
    }

    /**
     * Whether CAPI rejects this action when no filter is given.
     *
     * @return bool
     */
    protected function requiresFilters(): bool
    {
        return true;
    }
}
