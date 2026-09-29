<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Search;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Support\Guard;

/**
 * Item or collector search, the function behind Colnect's top bar search.
 *
 * GET /search[/collectibles/{type}]/q/{query}[/page/{page}]
 *
 * Returns {page, last_page, total_items, items}. Items are
 * [category_id, item_id, series_id, producer_id, front_picture_id,
 * back_picture_id, item_description, catalog_codes, item_name]; collectors are
 * [user_id, username, full_name].
 */
class GlobalSearchRequest extends Request
{
    /**
     * Collectable type searching collectors instead of items.
     */
    public const COLLECTORS = 'collectors';

    /**
     * The HTTP method of the request
     *
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * @var string|null
     */
    protected ?string $collectableType;

    /**
     * @var string
     */
    protected string $searchQuery;

    /**
     * @var int|null
     */
    protected ?int $page;

    /**
     * @param CategoryType|string|null $collectableType Category to search in, or
     *        "collectors" to search for collectors. Null searches every category.
     * @param string $searchQuery Required search query.
     * @param int|null $page Optional page number.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When inputs are empty or malformed.
     */
    public function __construct(CategoryType|string|null $collectableType, string $searchQuery, ?int $page = null)
    {
        $this->collectableType = $collectableType === null
            ? null
            : Guard::category($collectableType);

        $this->searchQuery = Guard::nonEmpty($searchQuery, 'Search query');

        if ($page !== null) {
            Guard::positiveInt($page, 'Page');
        }

        $this->page = $page;
    }

    /**
     * The endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        $endpoint = '/search';

        if ($this->collectableType !== null) {
            $endpoint .= '/collectibles/' . rawurlencode($this->collectableType);
        }

        $endpoint .= '/q/' . rawurlencode($this->searchQuery);

        if ($this->page !== null) {
            $endpoint .= '/page/' . $this->page;
        }

        return $endpoint;
    }
}
