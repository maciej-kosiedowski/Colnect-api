<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Requests\CategoryRequest;
use Slimad\ColnectApi\Support\Guard;

/**
 * Details of one or more items.
 *
 * GET /item/cat/{category}/id/{ids}[/include_field_ids/1][/include_details/1]
 *
 * Up to 50 IDs may be requested in a single call; passing more than one ID
 * makes Colnect answer with an array of arrays. The available fields differ
 * per category, see GetFieldTitlesRequest.
 */
class GetItemRequest extends CategoryRequest
{
    /**
     * Maximum number of item IDs accepted in a single call.
     */
    public const MAX_IDS = 50;

    protected string $ids;

    protected bool $includeFieldIds;

    protected bool $includeDetails;

    /**
     * @param int|string|array<int, int|string> $ids One ID, a comma separated list or a list of IDs.
     * @param bool $includeFieldIds Append the ID to each field name, for example "Gibraltar [2610]".
     * @param bool $includeDetails Return the names of referenced records as nested arrays.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the category or the IDs are malformed.
     */
    public function __construct(
        CategoryType|string $category,
        int|string|array $ids,
        bool $includeFieldIds = false,
        bool $includeDetails = false
    ) {
        $this->ids = Guard::idList($ids, 'Item IDs', self::MAX_IDS);
        $this->includeFieldIds = $includeFieldIds;
        $this->includeDetails = $includeDetails;

        parent::__construct($category);
    }

    protected function action(): string
    {
        return 'item';
    }

    protected function pathSuffix(): string
    {
        $suffix = '/id/' . $this->ids;

        if ($this->includeFieldIds) {
            $suffix .= '/include_field_ids/1';
        }

        if ($this->includeDetails) {
            $suffix .= '/include_details/1';
        }

        return $suffix;
    }
}
