<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Requests\General;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Vendor\ColnectApi\Requests\General\GetLanguagesRequest;

class GetLanguagesRequestTest extends TestCase
{
    public function test_it_resolves_endpoint(): void
    {
        $request = new GetLanguagesRequest();

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertEquals('/languages', $request->resolveEndpoint());
    }
}
