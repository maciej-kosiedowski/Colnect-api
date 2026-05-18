<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Enums\CategoryType;
use Vendor\ColnectApi\Requests\Category\GetItemsListRequest;

class GetItemsListRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_and_method_with_enum(): void
    {
        $request = new GetItemsListRequest(CategoryType::Banknotes);

        $this->assertEquals('/items/cat/banknotes', $request->resolveEndpoint());
        $this->assertEquals(Method::GET, $request->getMethod());
    }

    public function test_it_resolves_endpoint_and_method_with_string(): void
    {
        $request = new GetItemsListRequest('stamps');

        $this->assertEquals('/items/cat/stamps', $request->resolveEndpoint());
        $this->assertEquals(Method::GET, $request->getMethod());
    }
}
