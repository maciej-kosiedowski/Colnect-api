<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\General;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Requests\General\GetCategoriesRequest;

class GetCategoriesRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_and_method(): void
    {
        $request = new GetCategoriesRequest();

        $this->assertEquals('/categories', $request->resolveEndpoint());
        $this->assertEquals(Method::GET, $request->getMethod());
    }
}
