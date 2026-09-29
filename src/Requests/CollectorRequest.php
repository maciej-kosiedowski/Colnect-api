<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Slimad\ColnectApi\Support\Guard;

/**
 * Base class for the collector actions:
 *
 *     /[lang]/api/[app_id]/[action]/collector/[collector]
 *
 * Privacy settings may make Colnect answer "private" instead of the data.
 */
abstract class CollectorRequest extends Request
{
    protected Method $method = Method::GET;

    protected string $collector;

    /**
     * @param string $collector Colnect name of the collector.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the collector name is empty or malformed.
     */
    public function __construct(string $collector)
    {
        $this->collector = Guard::collector($collector);
    }

    /**
     * The CAPI action this request performs.
     */
    abstract protected function action(): string;

    /**
     * The collector this request is bound to.
     */
    public function getCollector(): string
    {
        return $this->collector;
    }

    public function resolveEndpoint(): string
    {
        return '/' . $this->action() . '/collector/' . rawurlencode($this->collector);
    }
}
