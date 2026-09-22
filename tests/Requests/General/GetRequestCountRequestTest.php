<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\General;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\General\GetRequestCountRequest;

class GetRequestCountRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_without_days(): void
    {
        $request = new GetRequestCountRequest();

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/request_count', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_days(): void
    {
        $request = new GetRequestCountRequest(30);

        $this->assertSame('/request_count/days/30', $request->resolveEndpoint());
    }

    public function test_it_accepts_the_maximum_number_of_days(): void
    {
        $request = new GetRequestCountRequest(GetRequestCountRequest::MAX_DAYS);

        $this->assertSame('/request_count/days/200', $request->resolveEndpoint());
    }

    public function test_it_rejects_more_than_the_maximum_number_of_days(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Days must be between 1 and 200.');

        new GetRequestCountRequest(201);
    }

    public function test_it_rejects_zero_days(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetRequestCountRequest(0);
    }
}
