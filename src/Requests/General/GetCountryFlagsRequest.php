<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\General;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Slimad\ColnectApi\Support\Guard;

/**
 * Flag picture IDs of the given countries.
 *
 * GET /country_flag/ids/{ids}
 *
 * Returns [country_id => flag_picture_id]; build the picture URL with
 * PictureUrl::flag().
 */
class GetCountryFlagsRequest extends Request
{
    protected Method $method = Method::GET;

    protected string $ids;

    /**
     * @param int|string|array<int, int|string> $ids One ID, a comma separated list or a list of country IDs.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the IDs are malformed.
     */
    public function __construct(int|string|array $ids)
    {
        $this->ids = Guard::idList($ids, 'Country IDs');
    }

    public function resolveEndpoint(): string
    {
        return '/country_flag/ids/' . $this->ids;
    }
}
