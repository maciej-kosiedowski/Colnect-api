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

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/countries/cat/stamps', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_string(): void
    {
        $request = new GetCountriesRequest('stamps');

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/countries/cat/stamps', $request->resolveEndpoint());
    }

    public function test_it_rejects_string_category_with_path_traversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetCountriesRequest('../admin');
    }

    public function test_it_rejects_string_category_with_invalid_characters_at_end(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GetCountriesRequest('stamps1');
    }
}
