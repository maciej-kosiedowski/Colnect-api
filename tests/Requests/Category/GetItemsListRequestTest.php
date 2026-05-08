<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Enums\CategoryType;
use Vendor\ColnectApi\Requests\Category\GetItemsListRequest;

class GetItemsListRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_with_string_category(): void
    {
        $request = new GetItemsListRequest('stamps');

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/items/cat/stamps', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_enum_category(): void
    {
        $request = new GetItemsListRequest(CategoryType::Coins);

        $this->assertEquals('/items/cat/coins', $request->resolveEndpoint());
    }
}
