<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Category;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Slimad\ColnectApi\Enums\CategoryType;

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
     *
     * @throws \InvalidArgumentException When a string category contains unexpected characters.
     */
    public function __construct(CategoryType|string $category)
    {
        if (is_string($category) && preg_match('/^[a-z_]+$/D', $category) !== 1) {
            throw new \InvalidArgumentException('Category must contain only lowercase letters and underscores.');
        }

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

        return '/items/cat/' . rawurlencode($categoryValue);
    }
}
