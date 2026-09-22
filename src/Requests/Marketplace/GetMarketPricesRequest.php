<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Marketplace;

use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Requests\CategoryRequest;
use Slimad\ColnectApi\Support\Guard;

/**
 * History of sale prices of the selected items, taken from the transactions
 * opened in the last {days} days.
 *
 * GET /market_prices/cat/{category}/ids/{ids}[/days/{days}]
 *
 * Returns {"item_id": {"condition": [[currency_id, price], ...]}}.
 */
class GetMarketPricesRequest extends CategoryRequest
{
    /**
     * @var string
     */
    protected string $ids;

    /**
     * @var int|null
     */
    protected ?int $days;

    /**
     * @param CategoryType|string $category
     * @param int|string|array<int, int|string> $ids One ID, a comma separated list or a list of item IDs.
     * @param int|null $days Number of days of history to take into account.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the category, IDs or days are malformed.
     */
    public function __construct(CategoryType|string $category, int|string|array $ids, ?int $days = null)
    {
        $this->ids = Guard::idList($ids, 'Item IDs');

        if ($days !== null) {
            Guard::positiveInt($days, 'Days');
        }

        $this->days = $days;

        parent::__construct($category);
    }

    /**
     * The CAPI action this request performs.
     *
     * @return string
     */
    protected function action(): string
    {
        return 'market_prices';
    }

    /**
     * Mandatory path segments placed between the category and the filters.
     *
     * @return string
     */
    protected function pathSuffix(): string
    {
        $suffix = '/ids/' . $this->ids;

        if ($this->days !== null) {
            $suffix .= '/days/' . $this->days;
        }

        return $suffix;
    }
}
