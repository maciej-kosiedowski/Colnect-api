<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Category\GetYearsRequest;
use Slimad\ColnectApi\Support\Filters;

class GetYearsRequestTest extends TestCase
{
    public function test_it_resolves_endpoint(): void
    {
        $request = new GetYearsRequest(CategoryType::Stamps, 2610);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/years/cat/stamps/country/2610', $request->resolveEndpoint());
    }

    public function test_it_uses_the_producer_filter_where_the_producer_is_not_the_country(): void
    {
        $this->assertSame('/years/cat/phonecards/producer/12', (new GetYearsRequest(CategoryType::Phonecards, 12))->resolveEndpoint());
        $this->assertSame('/years/cat/custom_category/producer/12', (new GetYearsRequest('custom_category', 12))->resolveEndpoint());
    }

    public function test_it_appends_filters_after_the_producer(): void
    {
        $request = new GetYearsRequest('stamps', 2610, Filters::make()->year(1980));

        $this->assertSame('/years/cat/stamps/country/2610/year/1980', $request->resolveEndpoint());
    }

    public function test_it_url_encodes_the_producer(): void
    {
        $request = new GetYearsRequest(CategoryType::Stamps, '../admin');

        $this->assertSame('/years/cat/stamps/country/..%2Fadmin', $request->resolveEndpoint());
    }

    public function test_it_rejects_an_empty_producer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Producer must not be empty.');

        new GetYearsRequest(CategoryType::Stamps, '');
    }

    public function test_it_rejects_a_malformed_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetYearsRequest('../admin', 2610);
    }
}
