<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Slimad\ColnectApi\ColnectConnector;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\General\GetLanguagesRequest;

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

    public function test_it_rejects_app_id_with_invalid_prefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('!app_123', 'secret_abc', 'en');
    }

    public function test_it_rejects_app_id_with_invalid_suffix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123!', 'secret_abc', 'en');
    }

    public function test_it_rejects_app_id_with_newline(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector("app_123\n", 'secret_abc', 'en');
    }

    public function test_it_rejects_language_with_invalid_prefix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123', 'secret_abc', '!en');
    }

    public function test_it_rejects_language_with_invalid_suffix(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123', 'secret_abc', 'en!');
    }

    public function test_it_rejects_language_with_newline(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ColnectConnector('app_123', 'secret_abc', "en\n");
    }

    public function test_it_accepts_locale_with_region(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'en_US');

        $this->assertEquals('https://api.colnect.net/en_US/api/app_123', $connector->resolveBaseUrl());
    }

    public function test_it_exposes_the_app_id_and_the_language(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'pl');

        $this->assertSame('app_123', $connector->getAppId());
        $this->assertSame('pl', $connector->getLanguage());
    }

    public function test_it_sends_a_custom_user_agent(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'en', 'MyCoolApp/1.2.3.4');

        $mockClient = new MockClient([
            GetLanguagesRequest::class => MockResponse::make(['success' => true]),
        ]);

        $connector->withMockClient($mockClient);
        $response = $connector->send(new GetLanguagesRequest());

        $this->assertSame('MyCoolApp/1.2.3.4', $response->getPendingRequest()->headers()->get('User-Agent'));
    }

    public function test_it_requests_http_2(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc');

        $mockClient = new MockClient([
            GetLanguagesRequest::class => MockResponse::make(['success' => true]),
        ]);

        $connector->withMockClient($mockClient);
        $response = $connector->send(new GetLanguagesRequest());

        $this->assertSame(2.0, $response->getPendingRequest()->config()->get('version'));
    }

    public function test_it_rejects_a_short_user_agent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User agent must be at least 16 characters long');

        new ColnectConnector('app_123', 'secret_abc', 'en', 'MyApp/1.0');
    }

    public function test_it_accepts_a_user_agent_of_the_minimum_length(): void
    {
        $userAgent = str_pad('MyApp/', ColnectConnector::MIN_USER_AGENT_LENGTH, '1');

        $connector = new ColnectConnector('app_123', 'secret_abc', 'en', $userAgent);

        $mockClient = new MockClient([
            GetLanguagesRequest::class => MockResponse::make(['success' => true]),
        ]);

        $connector->withMockClient($mockClient);
        $response = $connector->send(new GetLanguagesRequest());

        $this->assertSame($userAgent, $response->getPendingRequest()->headers()->get('User-Agent'));
    }

    public function test_it_rejects_a_user_agent_with_a_newline(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User agent must not contain control characters.');

        new ColnectConnector('app_123', 'secret_abc', 'en', "MyCoolApp/1.2.3.4\r\nX-Injected: 1");
    }

    public function test_it_rejects_an_empty_user_agent(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ColnectConnector('app_123', 'secret_abc', 'en', '');
    }
}
