<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Marketplace;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Enums\ItemsInSale;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Marketplace\GetItemSalesRequest;

class GetItemSalesRequestTest extends TestCase
{
    public function test_it_resolves_endpoint(): void
    {
        $request = new GetItemSalesRequest(CategoryType::Stamps, 35693);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/item_sales/cat/stamps/item_id/35693', $request->resolveEndpoint());
    }

    public function test_it_filters_single_items(): void
    {
        $request = new GetItemSalesRequest('stamps', 1, ItemsInSale::Single);

        $this->assertSame('/item_sales/cat/stamps/item_id/1/items_in_sale/single', $request->resolveEndpoint());
    }

    public function test_it_filters_lots(): void
    {
        $request = new GetItemSalesRequest('stamps', 1, ItemsInSale::Lots);

        $this->assertSame('/item_sales/cat/stamps/item_id/1/items_in_sale/lots', $request->resolveEndpoint());
    }

    public function test_it_requests_only_sale_ids(): void
    {
        $request = new GetItemSalesRequest('stamps', 1, null, true);

        $this->assertSame('/item_sales/cat/stamps/item_id/1/only_ids/1', $request->resolveEndpoint());
    }

    public function test_it_limits_the_number_of_sales(): void
    {
        $request = new GetItemSalesRequest('stamps', 1, null, false, 5);

        $this->assertSame('/item_sales/cat/stamps/item_id/1/max_sales/5', $request->resolveEndpoint());
    }

    public function test_it_combines_every_option(): void
    {
        $request = new GetItemSalesRequest(CategoryType::Stamps, [1, 2], ItemsInSale::Single, true, GetItemSalesRequest::MAX_SALES);

        $this->assertSame(
            '/item_sales/cat/stamps/item_id/1,2/items_in_sale/single/only_ids/1/max_sales/20',
            $request->resolveEndpoint()
        );
    }

    public function test_it_rejects_more_sales_than_the_maximum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max sales must be between 1 and 20.');

        new GetItemSalesRequest(CategoryType::Stamps, 1, null, false, 21);
    }

    public function test_it_rejects_zero_sales(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetItemSalesRequest(CategoryType::Stamps, 1, null, false, 0);
    }

    public function test_it_rejects_malformed_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetItemSalesRequest(CategoryType::Stamps, '../admin');
    }

    public function test_it_rejects_a_malformed_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetItemSalesRequest('../admin', 1);
    }
}
