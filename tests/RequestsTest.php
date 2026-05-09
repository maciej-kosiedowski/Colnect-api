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
    public function test_get_countries_request(): void
    {
        $requestEnum = new GetCountriesRequest(CategoryType::Stamps);
        $this->assertEquals('/countries/cat/stamps', $requestEnum->resolveEndpoint());
        $this->assertEquals('GET', $requestEnum->getMethod()->value);

        $requestString = new GetCountriesRequest('coins');
        $this->assertEquals('/countries/cat/coins', $requestString->resolveEndpoint());
    }

    public function test_get_items_list_request(): void
    {
        $requestEnum = new GetItemsListRequest(CategoryType::Banknotes);
        $this->assertEquals('/items/cat/banknotes', $requestEnum->resolveEndpoint());
        $this->assertEquals('GET', $requestEnum->getMethod()->value);

        $requestString = new GetItemsListRequest('medals');
        $this->assertEquals('/items/cat/medals', $requestString->resolveEndpoint());
    }

    public function test_get_categories_request(): void
    {
        $request = new GetCategoriesRequest();
        $this->assertEquals('/categories', $request->resolveEndpoint());
        $this->assertEquals('GET', $request->getMethod()->value);
    }

    public function test_get_languages_request(): void
    {
        $request = new GetLanguagesRequest();
        $this->assertEquals('/languages', $request->resolveEndpoint());
        $this->assertEquals('GET', $request->getMethod()->value);
    }

    public function test_global_search_request(): void
    {
        $requestWithoutPage = new GlobalSearchRequest('stamps', 'poland');
        $this->assertEquals('/search/collectibles/stamps/q/poland', $requestWithoutPage->resolveEndpoint());
        $this->assertEquals('GET', $requestWithoutPage->getMethod()->value);

        $requestWithPage = new GlobalSearchRequest('coins', 'gold', 2);
        $this->assertEquals('/search/collectibles/coins/q/gold/page/2', $requestWithPage->resolveEndpoint());
    }
}
