<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Support\Filters;
use Slimad\ColnectApi\Support\Guard;

/**
 * Base class for every action bound to a category:
 *
 *     /[lang]/api/[app_id]/[action]/cat/[category]/[filters]
 */
abstract class CategoryRequest extends Request
{
    protected Method $method = Method::GET;

    protected string $category;

    protected Filters $filters;

    /**
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When a string category contains unexpected characters or mandatory filters are missing.
     */
    public function __construct(CategoryType|string $category, ?Filters $filters = null)
    {
        $this->category = Guard::category($category);
        $this->filters = $filters ?? new Filters();

        if ($this->requiresFilters() && $this->filters->isEmpty()) {
            throw new InvalidArgumentException('The ' . $this->action() . ' action requires at least one filter.');
        }
    }

    /**
     * Whether CAPI rejects this action when no filter is given.
     */
    protected function requiresFilters(): bool
    {
        return false;
    }

    /**
     * The CAPI action this request performs.
     */
    abstract protected function action(): string;

    /**
     * Mandatory path segments placed between the category and the filters.
     */
    protected function pathSuffix(): string
    {
        return '';
    }

    /**
     * The category this request is bound to.
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * The filters applied to this request.
     */
    public function getFilters(): Filters
    {
        return $this->filters;
    }

    public function resolveEndpoint(): string
    {
        return '/' . $this->action() . '/cat/' . rawurlencode($this->category)
            . $this->pathSuffix()
            . $this->filters->toPath();
    }
}
