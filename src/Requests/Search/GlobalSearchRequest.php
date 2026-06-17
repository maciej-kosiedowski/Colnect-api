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
     * @param string $searchQuery Required search query
     * @param int|null $page Optional page number
     *
     * @throws \InvalidArgumentException When inputs are empty or malformed.
     */
    public function __construct(string $collectableType, string $searchQuery, ?int $page = null)
    {
        if (preg_match('/^[a-z_]+$/D', $collectableType) !== 1) {
            throw new \InvalidArgumentException('Collectable type must contain only lowercase letters and underscores.');
        }

        if ($searchQuery === '') {
            throw new \InvalidArgumentException('Search query must not be empty.');
        }

        if ($page !== null && $page < 1) {
            throw new \InvalidArgumentException('Page must be a positive integer.');
        }

        $this->collectableType = $collectableType;
        $this->searchQuery = $searchQuery;
        $this->page = $page;
    }

    /**
     * The endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        $endpoint = '/search/collectibles/' . rawurlencode($this->collectableType)
            . '/q/' . rawurlencode($this->searchQuery);

        if ($this->page !== null) {
            $endpoint .= '/page/' . $this->page;
        }

        return $endpoint;
    }
}
