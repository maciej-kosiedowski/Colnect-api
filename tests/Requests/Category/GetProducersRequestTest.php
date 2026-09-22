<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Category\GetProducersRequest;
use Slimad\ColnectApi\Support\Filters;

class GetProducersRequestTest extends TestCase
{
    public function test_it_resolves_endpoint(): void
    {
        $request = new GetProducersRequest(CategoryType::Phonecards, 12);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/producers/cat/phonecards/country/12', $request->resolveEndpoint());
    }

    public function test_it_appends_filters_after_the_country(): void
    {
        $request = new GetProducersRequest('phonecards', 12, Filters::make()->year(1998));

        $this->assertSame('/producers/cat/phonecards/country/12/year/1998', $request->resolveEndpoint());
    }

    public function test_it_url_encodes_the_country(): void
    {
        $request = new GetProducersRequest(CategoryType::Phonecards, '../admin');

        $this->assertSame('/producers/cat/phonecards/country/..%2Fadmin', $request->resolveEndpoint());
    }

    public function test_it_rejects_an_empty_country(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Country must not be empty.');

        new GetProducersRequest(CategoryType::Phonecards, '');
    }

    public function test_it_rejects_a_malformed_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetProducersRequest('../admin', 12);
    }
}
