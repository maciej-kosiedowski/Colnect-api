<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Vendor\ColnectApi\ColnectConnector;
use Vendor\ColnectApi\Requests\General\GetLanguagesRequest;

class ColnectConnectorTest extends TestCase
{
    public function test_it_resolves_base_url_correctly(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'en');

        $this->assertEquals('https://api.colnect.net/en/api/app_123', $connector->resolveBaseUrl());

        $connectorFr = new ColnectConnector('app_123', 'secret_abc', 'fr');
        $this->assertEquals('https://api.colnect.net/fr/api/app_123', $connectorFr->resolveBaseUrl());
    }

    public function test_it_adds_default_headers(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'en');
        $request = new GetLanguagesRequest();

        $mockClient = new MockClient([
            GetLanguagesRequest::class => MockResponse::make(['success' => true]),
        ]);

        $connector->withMockClient($mockClient);
        $response = $connector->send($request);

        $pendingRequest = $response->getPendingRequest();
        $headers = $pendingRequest->headers()->all();

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertEquals('ColnectSaloonClient/1.0.0', $headers['User-Agent']);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('application/json', $headers['Accept']);
    }

    public function test_it_adds_authentication_headers_correctly(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'en');
        $request = new GetLanguagesRequest();

        $mockClient = new MockClient([
            GetLanguagesRequest::class => MockResponse::make(['success' => true]),
        ]);

        $connector->withMockClient($mockClient);

        $response = $connector->send($request);

        $pendingRequest = $response->getPendingRequest();
        $headers = $pendingRequest->headers()->all();

        $this->assertArrayHasKey('Capi-Timestamp', $headers);
        $this->assertArrayHasKey('Capi-Hash', $headers);

        $sentTimestamp = $headers['Capi-Timestamp'];
        $this->assertIsString($sentTimestamp);
        $this->assertIsNumeric($sentTimestamp);
        $this->assertSame((string) (int) $sentTimestamp, $sentTimestamp); // Ensure it's exactly a string representation of an integer

        $path = $pendingRequest->getUri()->getPath();
        $expectedHash = hash_hmac('sha256', $path . '>|<' . $sentTimestamp, 'secret_abc');

        $this->assertEquals($expectedHash, $headers['Capi-Hash']);
    }
}
