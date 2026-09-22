<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Collector\GetRatingsCountRequest;
use Slimad\ColnectApi\Requests\Collector\GetRatingsGivenRequest;
use Slimad\ColnectApi\Requests\Collector\GetRatingsReceivedRequest;
use Slimad\ColnectApi\Requests\CollectorRequest;

class CollectorRequestTest extends TestCase
{
    /**
     * @return array<string, array{class-string<CollectorRequest>, string}>
     */
    public static function collectorActionProvider(): array
    {
        return [
            'ratings count' => [GetRatingsCountRequest::class, 'ratings_count'],
            'ratings received' => [GetRatingsReceivedRequest::class, 'ratings_received'],
            'ratings given' => [GetRatingsGivenRequest::class, 'ratings_given'],
        ];
    }

    /**
     * @param class-string<CollectorRequest> $class
     * @dataProvider collectorActionProvider
     */
    public function test_it_resolves_the_endpoint_of_every_collector_action(string $class, string $action): void
    {
        $request = new $class('some_collector');

        $this->assertEquals(Method::GET, $request->getMethod());
        $this->assertSame('/' . $action . '/collector/some_collector', $request->resolveEndpoint());
        $this->assertSame('some_collector', $request->getCollector());
    }

    public function test_it_url_encodes_the_collector_name(): void
    {
        $request = new GetRatingsCountRequest('collector+name');

        $this->assertSame('/ratings_count/collector/collector%2Bname', $request->resolveEndpoint());
    }

    public function test_it_rejects_an_empty_collector_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetRatingsCountRequest('');
    }

    public function test_it_rejects_a_collector_name_with_a_slash(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GetRatingsReceivedRequest('../admin');
    }
}
