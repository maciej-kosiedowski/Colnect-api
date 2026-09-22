<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Category\GetCatalogsRequest;
use Slimad\ColnectApi\Requests\Category\GetCategoryActionsRequest;
use Slimad\ColnectApi\Requests\Category\GetCategorySortOptionsRequest;
use Slimad\ColnectApi\Requests\Category\GetCountriesRequest;
use Slimad\ColnectApi\Requests\Category\GetCurrenciesRequest;
use Slimad\ColnectApi\Requests\Category\GetFaceValuesRequest;
use Slimad\ColnectApi\Requests\Category\GetProducerIsCountryRequest;
use Slimad\ColnectApi\Requests\Category\GetSystemsRequest;
use Slimad\ColnectApi\Requests\Category\GetThemesRequest;
use Slimad\ColnectApi\Requests\CategoryRequest;
use Slimad\ColnectApi\Support\Filters;

class CategoryRequestTest extends TestCase
{
    /**
     * @return array<string, array{class-string<CategoryRequest>, string}>
     */
    public static function categoryActionProvider(): array
    {
        return [
            'countries' => [GetCountriesRequest::class, 'countries'],
            'themes' => [GetThemesRequest::class, 'themes'],
            'catalogs' => [GetCatalogsRequest::class, 'catalogs'],
            'currencies' => [GetCurrenciesRequest::class, 'currencies'],
            'face values' => [GetFaceValuesRequest::class, 'face_values'],
            'systems' => [GetSystemsRequest::class, 'systems'],
            'producer is country' => [GetProducerIsCountryRequest::class, 'producer_is_country'],
            'category actions' => [GetCategoryActionsRequest::class, 'category_actions'],
            'category sort options' => [GetCategorySortOptionsRequest::class, 'category_sort_options'],
        ];
    }

    /**
     * @param class-string<CategoryRequest> $class
     * @dataProvider categoryActionProvider
     */
    public function test_it_resolves_the_endpoint_of_every_category_action(string $class, string $action): void
    {
        $request = new $class(CategoryType::Stamps);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/' . $action . '/cat/stamps', $request->resolveEndpoint());
    }

    /**
     * @param class-string<CategoryRequest> $class
     * @dataProvider categoryActionProvider
     */
    public function test_every_category_action_accepts_filters(string $class, string $action): void
    {
        $request = new $class('stamps', Filters::make()->country(5));

        $this->assertSame('/' . $action . '/cat/stamps/country/5', $request->resolveEndpoint());
    }

    public function test_it_exposes_the_category_and_the_filters(): void
    {
        $filters = Filters::make()->year(1980);
        $request = new GetCountriesRequest(CategoryType::Coins, $filters);

        $this->assertSame('coins', $request->getCategory());
        $this->assertSame($filters, $request->getFilters());
        $this->assertSame(['year' => '1980'], $request->getFilters()->toArray());
    }

    public function test_it_defaults_to_an_empty_filter_collection(): void
    {
        $request = new GetCountriesRequest(CategoryType::Coins);

        $this->assertTrue($request->getFilters()->isEmpty());
    }

    public function test_it_rejects_a_malformed_string_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetThemesRequest('../admin');
    }
}
