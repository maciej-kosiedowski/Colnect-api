<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\General;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Slimad\ColnectApi\Support\Guard;

/**
 * Number of CAPI requests made per day by the calling application.
 *
 * GET /request_count[/days/{days}]
 *
 * Returns [day, request_count].
 */
class GetRequestCountRequest extends Request
{
    /**
     * Maximum number of days Colnect reports on.
     */
    public const MAX_DAYS = 200;

    /**
     * The HTTP method of the request
     *
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * @var int|null
     */
    protected ?int $days;

    /**
     * @param int|null $days Number of days to report on, 1 to 200.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When days is out of range.
     */
    public function __construct(?int $days = null)
    {
        if ($days !== null) {
            Guard::between($days, 1, self::MAX_DAYS, 'Days');
        }

        $this->days = $days;
    }

    /**
     * The endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        if ($this->days === null) {
            return '/request_count';
        }

        return '/request_count/days/' . $this->days;
    }
}
