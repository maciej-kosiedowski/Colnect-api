<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Search;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Requests\Search\GlobalSearchRequest;

class GlobalSearchRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_without_page(): void
    {
        $request = new GlobalSearchRequest('stamps', 'blue');

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/search/collectibles/stamps/q/blue', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_page(): void
    {
        $request = new GlobalSearchRequest('stamps', 'blue', 2);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/search/collectibles/stamps/q/blue/page/2', $request->resolveEndpoint());
    }

    public function test_it_url_encodes_search_query(): void
    {
        $request = new GlobalSearchRequest('stamps', 'blue & red');

        $this->assertEquals('/search/collectibles/stamps/q/blue%20%26%20red', $request->resolveEndpoint());
    }

    public function test_it_url_encodes_path_traversal_attempt(): void
    {
        $request = new GlobalSearchRequest('stamps', '../../admin');

        $endpoint = $request->resolveEndpoint();
        $this->assertStringNotContainsString('../', $endpoint);
        $this->assertEquals('/search/collectibles/stamps/q/..%2F..%2Fadmin', $endpoint);
    }

    public function test_it_rejects_invalid_collectable_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GlobalSearchRequest('../stamps', 'blue');
    }

    public function test_it_rejects_empty_search_query(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GlobalSearchRequest('stamps', '');
    }

    public function test_it_rejects_non_positive_page(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GlobalSearchRequest('stamps', 'blue', 0);
    }

    public function test_it_accepts_page_equal_to_one(): void
    {
        $request = new GlobalSearchRequest('stamps', 'blue', 1);
        $this->assertEquals('/search/collectibles/stamps/q/blue/page/1', $request->resolveEndpoint());
    }

    public function test_it_rejects_collectable_type_with_invalid_prefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GlobalSearchRequest('!stamps', 'blue');
    }

    public function test_it_rejects_collectable_type_with_invalid_suffix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GlobalSearchRequest('stamps!', 'blue');
    }

    public function test_it_rejects_collectable_type_with_newline(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GlobalSearchRequest("stamps\n", 'blue');
    }
}
