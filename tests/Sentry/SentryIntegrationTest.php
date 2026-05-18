<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Tests\Sentry;

use PHPUnit\Framework\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Sentry\Breadcrumb;
use Sentry\ClientBuilder;
use Sentry\Event;
use Sentry\SentrySdk;
use Sentry\State\Hub;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;
use Vendor\ColnectApi\ColnectConnector;
use Vendor\ColnectApi\Requests\General\GetLanguagesRequest;
use Vendor\ColnectApi\Sentry\SentryIntegration;

class SentryIntegrationTest extends TestCase
{
    /**
     * @var list<Event>
     */
    private array $capturedEvents = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->capturedEvents = [];

        $sink = function (Event $event): void {
            $this->capturedEvents[] = $event;
        };

        $transport = new class ($sink) implements TransportInterface {
            /**
             * @param callable(Event): void $sink
             */
            public function __construct(private $sink)
            {
            }

            public function send(Event $event): Result
            {
                ($this->sink)($event);

                return new Result(ResultStatus::success(), $event);
            }

            public function close(?int $timeout = null): Result
            {
                return new Result(ResultStatus::success());
            }
        };

        $client = ClientBuilder::create([
            'dsn' => 'https://public@example.com/1',
            'default_integrations' => false,
        ])
            ->setTransport($transport)
            ->getClient();

        $hub = new Hub($client);
        SentrySdk::setCurrentHub($hub);
    }

    public function test_is_available_returns_true_when_sentry_sdk_is_installed(): void
    {
        $this->assertTrue(SentryIntegration::isAvailable());
    }

    public function test_register_attaches_middleware_and_records_request_breadcrumb(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'en');
        SentryIntegration::register($connector);

        $connector->withMockClient(new MockClient([
            GetLanguagesRequest::class => MockResponse::make(['success' => true], status: 200),
        ]));

        $response = $connector->send(new GetLanguagesRequest());

        $this->assertSame(200, $response->status());

        $hub = SentrySdk::getCurrentHub();
        $hub->captureMessage('flush');

        $this->assertNotEmpty($this->capturedEvents, 'Expected Sentry event to be captured.');

        $breadcrumbs = $this->capturedEvents[0]->getBreadcrumbs();
        $messages = array_map(static fn (Breadcrumb $b): string => (string) $b->getMessage(), $breadcrumbs);

        $this->assertTrue(
            (bool) array_filter($messages, static fn (string $m): bool => str_contains($m, 'GET') && str_contains($m, '/languages')),
            'Expected request breadcrumb to include GET and /languages.',
        );

        $this->assertTrue(
            (bool) array_filter($messages, static fn (string $m): bool => str_contains($m, '-> 200')),
            'Expected response breadcrumb to include status code.',
        );
    }

    public function test_failed_response_captures_exception(): void
    {
        $connector = new ColnectConnector('app_123', 'secret_abc', 'en');
        SentryIntegration::register($connector);

        $connector->withMockClient(new MockClient([
            GetLanguagesRequest::class => MockResponse::make(['error' => 'boom'], status: 500),
        ]));

        $response = $connector->send(new GetLanguagesRequest());

        $this->assertSame(500, $response->status());
        $this->assertTrue($response->failed());

        $this->assertNotEmpty($this->capturedEvents, 'Expected Sentry to receive the failed response exception.');

        $exceptionEvent = null;
        foreach ($this->capturedEvents as $event) {
            if ($event->getExceptions() !== []) {
                $exceptionEvent = $event;
                break;
            }
        }

        $this->assertNotNull($exceptionEvent, 'Expected at least one event with an exception payload.');
    }

    public function test_capture_fatal_exception_sends_event(): void
    {
        SentryIntegration::captureFatalException(new \RuntimeException('boom'));

        $this->assertNotEmpty($this->capturedEvents);

        $found = false;
        foreach ($this->capturedEvents as $event) {
            foreach ($event->getExceptions() as $exception) {
                if ($exception->getValue() === 'boom') {
                    $found = true;
                    break 2;
                }
            }
        }

        $this->assertTrue($found, 'Expected a captured event referencing the RuntimeException.');
    }
}
