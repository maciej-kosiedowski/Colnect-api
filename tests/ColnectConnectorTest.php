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

        /** @var string $sentTimestamp */
        $sentTimestamp = $headers['Capi-Timestamp'];
        $this->assertSame((string) (int) $sentTimestamp, $sentTimestamp);

        $path = (string) parse_url($pendingRequest->getUrl(), PHP_URL_PATH);
        $expectedHash = hash_hmac('sha256', $path . '>|<' . $sentTimestamp, 'secret_abc');

        $this->assertEquals($expectedHash, $headers['Capi-Hash']);
    }

    public function test_it_rejects_app_id_with_slashes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('../evil', 'secret_abc', 'en');
    }

    public function test_it_rejects_app_id_with_invalid_characters_at_end(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123!', 'secret_abc', 'en');
    }

    public function test_it_rejects_empty_app_secret(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123', '', 'en');
    }

    public function test_it_rejects_language_with_path_traversal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123', 'secret_abc', '../../evil.com/x');
    }

    public function test_it_rejects_language_with_invalid_characters_at_start(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123', 'secret_abc', '!en');
    }

    public function test_it_rejects_language_with_invalid_characters_at_end(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123', 'secret_abc', 'en!');
    }

    public function test_it_accepts_locale_with_region(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'en_US');

        $this->assertEquals('https://api.colnect.net/en_US/api/app_123', $connector->resolveBaseUrl());
    }

    public function test_it_rejects_invalid_url_in_boot(): void
    {
        $connector = new class('app_123', 'secret_abc', 'en') extends ColnectConnector {
            public function resolveBaseUrl(): string {
                return 'http:///example.com:80'; // an invalid base URL
            }
        };

        $request = new \Vendor\ColnectApi\Requests\General\GetLanguagesRequest();

        $mockClient = new \Saloon\Http\Faking\MockClient([
            \Vendor\ColnectApi\Requests\General\GetLanguagesRequest::class => \Saloon\Http\Faking\MockResponse::make(['success' => true]),
        ]);

        $connector->withMockClient($mockClient);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL provided.');

        $connector->send($request);
    }
}
