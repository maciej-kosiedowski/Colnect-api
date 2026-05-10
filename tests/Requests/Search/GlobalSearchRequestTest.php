<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Requests\Search;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Requests\Search\GlobalSearchRequest;

class GlobalSearchRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_without_page(): void
    {
        $request = new GlobalSearchRequest('stamps', 'france');
        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/search/collectibles/stamps/q/france', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_page(): void
    {
        $request = new GlobalSearchRequest('coins', 'gold', 2);
        $this->assertEquals('/search/collectibles/coins/q/gold/page/2', $request->resolveEndpoint());
    }
}
