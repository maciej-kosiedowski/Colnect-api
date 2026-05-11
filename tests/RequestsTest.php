<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests;

use PHPUnit\Framework\TestCase;
use Vendor\ColnectApi\Enums\CategoryType;
use Vendor\ColnectApi\Requests\Category\GetCountriesRequest;
use Vendor\ColnectApi\Requests\Category\GetItemsListRequest;
use Vendor\ColnectApi\Requests\General\GetCategoriesRequest;
use Vendor\ColnectApi\Requests\General\GetLanguagesRequest;
use Vendor\ColnectApi\Requests\Search\GlobalSearchRequest;

class RequestsTest extends TestCase
{
    public function test_get_categories_request(): void
    {
        $request = new GetCategoriesRequest();
        $this->assertEquals('/categories', $request->resolveEndpoint());
    }

    public function test_get_languages_request(): void
    {
        $request = new GetLanguagesRequest();
        $this->assertEquals('/languages', $request->resolveEndpoint());
    }

    public function test_get_countries_request(): void
    {
        $request1 = new GetCountriesRequest(CategoryType::Stamps);
        $this->assertEquals('/countries/cat/stamps', $request1->resolveEndpoint());

        $request2 = new GetCountriesRequest('coins');
        $this->assertEquals('/countries/cat/coins', $request2->resolveEndpoint());
    }

    public function test_get_items_list_request(): void
    {
        $request1 = new GetItemsListRequest(CategoryType::Stamps);
        $this->assertEquals('/items/cat/stamps', $request1->resolveEndpoint());

        $request2 = new GetItemsListRequest('coins');
        $this->assertEquals('/items/cat/coins', $request2->resolveEndpoint());
    }

    public function test_global_search_request(): void
    {
        $request1 = new GlobalSearchRequest('stamps', 'test');
        $this->assertEquals('/search/collectibles/stamps/q/test', $request1->resolveEndpoint());

        $request2 = new GlobalSearchRequest('coins', 'gold', 2);
        $this->assertEquals('/search/collectibles/coins/q/gold/page/2', $request2->resolveEndpoint());
    }
}
