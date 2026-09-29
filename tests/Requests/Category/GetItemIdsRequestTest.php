<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Category\GetItemIdsRequest;
use Slimad\ColnectApi\Support\Filters;

class GetItemIdsRequestTest extends TestCase
{
    public function test_it_resolves_endpoint(): void
    {
        $request = new GetItemIdsRequest(CategoryType::Stamps, Filters::make()->producer(2610)->year(1980));

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/list_id/cat/stamps/producer/2610/year/1980', $request->resolveEndpoint());
    }

    public function test_it_requires_at_least_one_filter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The list_id action requires at least one filter.');

        new GetItemIdsRequest(CategoryType::Stamps);
    }

    public function test_it_rejects_a_malformed_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetItemIdsRequest('../admin', Filters::make()->country(5));
    }
}
