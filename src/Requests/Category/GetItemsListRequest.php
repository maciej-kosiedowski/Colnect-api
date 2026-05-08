<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Requests\Category;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Vendor\ColnectApi\Enums\CategoryType;

class GetItemsListRequest extends Request
{
    /**
     * The HTTP method of the request
     *
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * @var CategoryType|string
     */
    protected CategoryType|string $category;

    /**
     * @param CategoryType|string $category
     */
    public function __construct(CategoryType|string $category)
    {
        $this->category = $category;
    }

    /**
     * The endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        $categoryValue = $this->category instanceof CategoryType ? $this->category->value : $this->category;

        return "/items/cat/{$categoryValue}";
    }
}
