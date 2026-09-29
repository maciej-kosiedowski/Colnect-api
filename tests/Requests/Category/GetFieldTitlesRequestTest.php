<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Category;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Category\GetFieldTitlesRequest;

class GetFieldTitlesRequestTest extends TestCase
{
    public function test_it_resolves_endpoint(): void
    {
        $request = new GetFieldTitlesRequest(CategoryType::Coins);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/field_titles/cat/coins', $request->resolveEndpoint());
    }

    public function test_it_requests_raw_class_names(): void
    {
        $request = new GetFieldTitlesRequest('stamps', true);

        $this->assertSame('/field_titles/cat/stamps/get_class_names/1', $request->resolveEndpoint());
    }

    public function test_it_rejects_a_malformed_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetFieldTitlesRequest('../admin');
    }
}
