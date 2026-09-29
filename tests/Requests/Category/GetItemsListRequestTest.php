<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Category\GetItemsListRequest;
use Slimad\ColnectApi\Support\Filters;

class GetItemsListRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_with_enum(): void
    {
        $request = new GetItemsListRequest(CategoryType::Coins, Filters::make()->country(5));

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/list/cat/coins/country/5', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_string(): void
    {
        $request = new GetItemsListRequest('coins', Filters::make()->country(5));

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/list/cat/coins/country/5', $request->resolveEndpoint());
    }

    public function test_it_supports_sorting(): void
    {
        $request = new GetItemsListRequest(CategoryType::Coins, Filters::make()->country(5)->sort('face_value'));

        $this->assertSame('/list/cat/coins/country/5/sort/face_value', $request->resolveEndpoint());
    }

    public function test_it_requires_at_least_one_filter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The list action requires at least one filter.');

        new GetItemsListRequest(CategoryType::Coins);
    }

    public function test_it_rejects_an_empty_filter_collection(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetItemsListRequest(CategoryType::Coins, new Filters());
    }

    public function test_it_rejects_string_category_with_path_traversal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GetItemsListRequest('../admin', Filters::make()->country(5));
    }

    public function test_it_rejects_string_category_with_invalid_prefix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GetItemsListRequest('!coins', Filters::make()->country(5));
    }

    public function test_it_rejects_string_category_with_invalid_suffix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GetItemsListRequest('coins!', Filters::make()->country(5));
    }

    public function test_it_rejects_string_category_with_newline(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GetItemsListRequest("coins\n", Filters::make()->country(5));
    }
}
