<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\General;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\General\GetCountryFlagsRequest;

class GetCountryFlagsRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_for_a_list_of_ids(): void
    {
        $request = new GetCountryFlagsRequest([5, 7, 469]);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/country_flag/ids/5,7,469', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_for_a_single_id(): void
    {
        $request = new GetCountryFlagsRequest(469);

        $this->assertSame('/country_flag/ids/469', $request->resolveEndpoint());
    }

    public function test_it_rejects_malformed_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetCountryFlagsRequest('5,../admin');
    }
}
