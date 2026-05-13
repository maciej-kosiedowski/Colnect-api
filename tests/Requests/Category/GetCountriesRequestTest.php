<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Enums\CategoryType;
use Vendor\ColnectApi\Requests\Category\GetCountriesRequest;

class GetCountriesRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_with_enum(): void
    {
        $request = new GetCountriesRequest(CategoryType::Stamps);

        $this->assertEquals('/countries/cat/stamps', $request->resolveEndpoint());
        $this->assertEquals(Method::GET, $request->getMethod());
    }

    public function test_it_resolves_endpoint_with_string(): void
    {
        $request = new GetCountriesRequest('coins');

        $this->assertEquals('/countries/cat/coins', $request->resolveEndpoint());
    }
}
