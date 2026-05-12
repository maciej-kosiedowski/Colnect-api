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
        $request = new GlobalSearchRequest('stamps', 'test');
        $this->assertEquals('/search/collectibles/stamps/q/test', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_page(): void
    {
        $request = new GlobalSearchRequest('stamps', 'test', 2);
        $this->assertEquals('/search/collectibles/stamps/q/test/page/2', $request->resolveEndpoint());
    }

    public function test_it_uses_get_method(): void
    {
        $request = new GlobalSearchRequest('stamps', 'test');
        $this->assertEquals(Method::GET, $request->getMethod());
    }
}
