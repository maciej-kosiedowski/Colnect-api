<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Requests\CategoryRequest;

/**
 * All fields available in the category, in the order GetItemRequest returns
 * them. Fields differ per category and may change, so confirm them with this
 * action before mapping item data.
 *
 * GET /field_titles/cat/{category}[/get_class_names/1]
 */
class GetFieldTitlesRequest extends CategoryRequest
{
    /**
     * @var bool
     */
    protected bool $getClassNames;

    /**
     * @param CategoryType|string $category
     * @param bool $getClassNames Output raw class names instead of display titles
     *                            for multi-value fields. Kept by Colnect for
     *                            backward compatibility only.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When a string category contains unexpected characters.
     */
    public function __construct(CategoryType|string $category, bool $getClassNames = false)
    {
        $this->getClassNames = $getClassNames;

        parent::__construct($category);
    }

    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'field_titles';
    }

    /**
     * Mandatory path segments placed between the category and the filters.
     *
     * @return string
     */
    protected function pathSuffix(): string
    {
        return $this->getClassNames ? '/get_class_names/1' : '';
    }
}
