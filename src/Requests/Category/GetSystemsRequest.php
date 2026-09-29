<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of systems. Filters are optional; returns [system_id, system_name, item_count].
 *
 * GET /systems/cat/{category}
 */
class GetSystemsRequest extends CategoryRequest
{
    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'systems';
    }
}
