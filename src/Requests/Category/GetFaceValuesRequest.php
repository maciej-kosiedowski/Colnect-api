<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * List of face values. Filters are optional; returns [face_value_id, face_value_name, item_count].
 *
 * GET /face_values/cat/{category}
 */
class GetFaceValuesRequest extends CategoryRequest
{
    protected function action(): string
    {
        return 'face_values';
    }
}
