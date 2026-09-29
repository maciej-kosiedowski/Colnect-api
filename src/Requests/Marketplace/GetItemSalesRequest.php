<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Marketplace;

use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Enums\ItemsInSale;
use Slimad\ColnectApi\Requests\CategoryRequest;
use Slimad\ColnectApi\Support\Guard;

/**
 * Market sales of the selected items.
 *
 * GET /item_sales/cat/{category}/item_id/{ids}[/items_in_sale/{single|lots}]
 *     [/only_ids/1][/max_sales/{1-20}]
 *
 * Returns {"sale_id": {"price": .., "currency": .., "expiry_date": ..,
 * "sellers_country": .., "items_in_sale": .., "remaining_quantity": ..,
 * "link": .., "seller": .., "shipping_methods": .., "payment_methods": ..,
 * "condition": ..}}, or an array of sale IDs when onlyIds is used.
 */
class GetItemSalesRequest extends CategoryRequest
{
    /**
     * Maximum number of sales Colnect returns in a single call.
     */
    public const MAX_SALES = 20;

    /**
     * @var string
     */
    protected string $ids;

    /**
     * @var ItemsInSale|null
     */
    protected ?ItemsInSale $itemsInSale;

    /**
     * @var bool
     */
    protected bool $onlyIds;

    /**
     * @var int|null
     */
    protected ?int $maxSales;

    /**
     * @param CategoryType|string $category
     * @param int|string|array<int, int|string> $ids One ID, a comma separated list or a list of item IDs.
     * @param ItemsInSale|null $itemsInSale Filter by the number of items in the sale.
     * @param bool $onlyIds Return only the sale IDs.
     * @param int|null $maxSales Maximum number of sales to return, 1 to 20.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the category, IDs or maxSales are malformed.
     */
    public function __construct(
        CategoryType|string $category,
        int|string|array $ids,
        ?ItemsInSale $itemsInSale = null,
        bool $onlyIds = false,
        ?int $maxSales = null
    ) {
        $this->ids = Guard::idList($ids, 'Item IDs');

        if ($maxSales !== null) {
            Guard::between($maxSales, 1, self::MAX_SALES, 'Max sales');
        }

        $this->itemsInSale = $itemsInSale;
        $this->onlyIds = $onlyIds;
        $this->maxSales = $maxSales;

        parent::__construct($category);
    }

    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'item_sales';
    }

    /**
     * Mandatory path segments placed between the category and the filters.
     *
     * @return string
     */
    protected function pathSuffix(): string
    {
        $suffix = '/item_id/' . $this->ids;

        if ($this->itemsInSale !== null) {
            $suffix .= '/items_in_sale/' . $this->itemsInSale->value;
        }

        if ($this->onlyIds) {
            $suffix .= '/only_ids/1';
        }

        if ($this->maxSales !== null) {
            $suffix .= '/max_sales/' . $this->maxSales;
        }

        return $suffix;
    }
}
