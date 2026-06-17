<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\General;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Requests\General\GetCategoriesRequest;

class GetCategoriesRequestTest extends TestCase
{
    public function test_it_resolves_endpoint(): void
    {
        $request = new GetCategoriesRequest();

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/categories', $request->resolveEndpoint());
    }
}
