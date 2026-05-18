<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Search;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Requests\Search\GlobalSearchRequest;

class GlobalSearchRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_and_method_without_page(): void
    {
        $request = new GlobalSearchRequest('stamps', 'france');

        $this->assertEquals('/search/collectibles/stamps/q/france', $request->resolveEndpoint());
        $this->assertEquals(Method::GET, $request->getMethod());
    }

    public function test_it_resolves_endpoint_and_method_with_page(): void
    {
        $request = new GlobalSearchRequest('coins', 'gold', 2);

        $this->assertEquals('/search/collectibles/coins/q/gold/page/2', $request->resolveEndpoint());
        $this->assertEquals(Method::GET, $request->getMethod());
    }
}
