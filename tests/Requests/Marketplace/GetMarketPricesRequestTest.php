<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Marketplace;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Marketplace\GetMarketPricesRequest;

class GetMarketPricesRequestTest extends TestCase
{
    public function test_it_resolves_endpoint_without_days(): void
    {
        $request = new GetMarketPricesRequest(CategoryType::Coins, [5, 7]);

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/market_prices/cat/coins/ids/5,7', $request->resolveEndpoint());
    }

    public function test_it_resolves_endpoint_with_days(): void
    {
        $request = new GetMarketPricesRequest('coins', 5, 30);

        $this->assertSame('/market_prices/cat/coins/ids/5/days/30', $request->resolveEndpoint());
    }

    public function test_it_rejects_malformed_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetMarketPricesRequest(CategoryType::Coins, 'abc');
    }

    public function test_it_rejects_non_positive_days(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Days must be a positive integer.');

        new GetMarketPricesRequest(CategoryType::Coins, 5, 0);
    }

    public function test_it_rejects_a_malformed_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetMarketPricesRequest('../admin', 5);
    }
}
