<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Requests\General;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Requests\General\GetCategoriesRequest;
use Vendor\ColnectApi\Requests\General\GetLanguagesRequest;

class GetCategoriesRequestTest extends TestCase
{
    public function test_it_resolves_categories_endpoint(): void
    {
        $request = new GetCategoriesRequest();

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/categories', $request->resolveEndpoint());
    }

    public function test_it_resolves_languages_endpoint(): void
    {
        $request = new GetLanguagesRequest();

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/languages', $request->resolveEndpoint());
    }
}
