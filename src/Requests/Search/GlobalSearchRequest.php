<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Requests\Search;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GlobalSearchRequest extends Request
{
    /**
     * The HTTP method of the request
     *
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * @var string
     */
    protected string $collectableType;

    /**
     * @var string
     */
    protected string $searchQuery;

    /**
     * @var int|null
     */
    protected ?int $page;

    /**
     * @param string $collectableType Type of collectable (e.g. 'stamps', 'collectors')
     * @param string $query Required search query
     * @param int|null $page Optional page number
     */
    public function __construct(string $collectableType, string $query, ?int $page = null)
    {
        $this->collectableType = $collectableType;
        $this->searchQuery = $query;
        $this->page = $page;
    }

    /**
     * The endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        $endpoint = "/search/collectibles/{$this->collectableType}/q/{$this->searchQuery}";

        if ($this->page !== null) {
            $endpoint .= "/page/{$this->page}";
        }

        return $endpoint;
    }
}
