<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Category\GetItemRequest;

class GetItemRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_for_a_single_item(): void
    {
        $request = new GetItemRequest(CategoryType::Coins, 35693);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/item/cat/coins/id/35693', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_for_multiple_items(): void
    {
        $request = new GetItemRequest('coins', [1, 2, 3]);

        $this->assertSame('/item/cat/coins/id/1,2,3', $request->resolveEndpoint());
    }

    public function test_it_accepts_a_comma_separated_list_of_ids(): void
    {
        $request = new GetItemRequest(CategoryType::Coins, '1,2');

        $this->assertSame('/item/cat/coins/id/1,2', $request->resolveEndpoint());
    }

    public function test_it_appends_include_field_ids(): void
    {
        $request = new GetItemRequest(CategoryType::Coins, 1, true);

        $this->assertSame('/item/cat/coins/id/1/include_field_ids/1', $request->resolveEndpoint());
    }

    public function test_it_appends_include_details(): void
    {
        $request = new GetItemRequest(CategoryType::Coins, 1, false, true);

        $this->assertSame('/item/cat/coins/id/1/include_details/1', $request->resolveEndpoint());
    }

    public function test_it_appends_both_options(): void
    {
        $request = new GetItemRequest(CategoryType::Coins, 1, true, true);

        $this->assertSame('/item/cat/coins/id/1/include_field_ids/1/include_details/1', $request->resolveEndpoint());
    }

    public function test_it_accepts_fifty_ids(): void
    {
        $request = new GetItemRequest(CategoryType::Coins, range(1, GetItemRequest::MAX_IDS));

        $this->assertSame('/item/cat/coins/id/' . implode(',', range(1, 50)), $request->resolveEndpoint());
    }

    public function test_it_rejects_more_than_fifty_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item IDs must not contain more than 50 IDs.');

        new GetItemRequest(CategoryType::Coins, range(1, 51));
    }

    public function test_it_rejects_non_numeric_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetItemRequest(CategoryType::Coins, '../admin');
    }

    public function test_it_rejects_a_malformed_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetItemRequest('../admin', 1);
    }
}
