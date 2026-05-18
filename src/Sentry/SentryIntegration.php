<?php

declare(strict_types=1);

namespace Vendor\ColnectApi\Sentry;

use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Throwable;

/**
 * Optional Sentry integration for the Colnect connector.
 *
 * Registers Saloon middleware that records HTTP breadcrumbs for every
 * outgoing request and reports failed responses or fatal transport
 * exceptions to Sentry. The host application is responsible for calling
 * `\Sentry\init()` during bootstrap; when the `sentry/sentry` package
 * is not installed the integration becomes a safe no-op so the SDK can
 * still be used without the runtime dependency.
 */
final class SentryIntegration
{
    private const MIDDLEWARE_PREFIX = 'colnect-sentry';

    private function __construct()
    {
    }

    /**
     * Attach Sentry breadcrumbs and exception reporting to a connector.
     */
    public static function register(Connector $connector): Connector
    {
        $middleware = $connector->middleware();

        $middleware->onRequest(
            static fn (PendingRequest $pendingRequest): PendingRequest => self::recordRequestBreadcrumb($pendingRequest),
            self::MIDDLEWARE_PREFIX . '-request',
        );

        $middleware->onResponse(
            static fn (Response $response): Response => self::recordResponse($response),
            self::MIDDLEWARE_PREFIX . '-response',
        );

        $middleware->onFatalException(
            static function (Throwable $throwable): void {
                self::captureFatalException($throwable);
            },
            self::MIDDLEWARE_PREFIX . '-fatal',
        );

        return $connector;
    }

    public static function recordRequestBreadcrumb(PendingRequest $pendingRequest): PendingRequest
    {
        if (! self::isAvailable()) {
            return $pendingRequest;
        }

        \Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
            \Sentry\Breadcrumb::LEVEL_INFO,
            \Sentry\Breadcrumb::TYPE_HTTP,
            'http.client',
            sprintf('%s %s', $pendingRequest->getMethod()->value, $pendingRequest->getUrl()),
            [
                'url' => $pendingRequest->getUrl(),
                'method' => $pendingRequest->getMethod()->value,
            ],
        ));

        return $pendingRequest;
    }

    public static function recordResponse(Response $response): Response
    {
        if (! self::isAvailable()) {
            return $response;
        }

        $pendingRequest = $response->getPendingRequest();
        $status = $response->status();
        $failed = $response->failed();

        \Sentry\addBreadcrumb(new \Sentry\Breadcrumb(
            $failed ? \Sentry\Breadcrumb::LEVEL_ERROR : \Sentry\Breadcrumb::LEVEL_INFO,
            \Sentry\Breadcrumb::TYPE_HTTP,
            'http.client',
            sprintf(
                '%s %s -> %d',
                $pendingRequest->getMethod()->value,
                $pendingRequest->getUrl(),
                $status,
            ),
            [
                'url' => $pendingRequest->getUrl(),
                'method' => $pendingRequest->getMethod()->value,
                'status_code' => $status,
            ],
        ));

        if ($failed) {
            $exception = $response->toException();
            if ($exception instanceof Throwable) {
                \Sentry\captureException($exception);
            }
        }

        return $response;
    }

    public static function captureFatalException(Throwable $throwable): void
    {
        if (! self::isAvailable()) {
            return;
        }

        \Sentry\captureException($throwable);
    }

    public static function isAvailable(): bool
    {
        return class_exists(\Sentry\Breadcrumb::class)
            && function_exists('Sentry\\addBreadcrumb')
            && function_exists('Sentry\\captureException');
    }
}
