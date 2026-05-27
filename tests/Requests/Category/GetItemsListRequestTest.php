<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Enums\CategoryType;
use Vendor\ColnectApi\Requests\Category\GetItemsListRequest;

class GetItemsListRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_with_enum(): void
    {
        $request = new GetItemsListRequest(CategoryType::Coins);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/items/cat/coins', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_string(): void
    {
        $request = new GetItemsListRequest('coins');

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/items/cat/coins', $request->resolveEndpoint());
    }

    public function test_it_rejects_string_category_with_path_traversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetItemsListRequest('../admin');
    }

    public function test_it_rejects_string_category_with_invalid_prefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetItemsListRequest('!coins');
    }

    public function test_it_rejects_string_category_with_invalid_suffix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetItemsListRequest('coins!');
    }
}
