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
        $request = new GlobalSearchRequest('stamps', 'poland');

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/search/collectibles/stamps/q/poland', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_page(): void
    {
        $request = new GlobalSearchRequest('stamps', 'poland', 2);

        $this->assertEquals('/search/collectibles/stamps/q/poland/page/2', $request->resolveEndpoint());
    }
}
