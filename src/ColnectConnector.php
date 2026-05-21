<?php

declare(strict_types=1);

namespace Vendor\ColnectApi;

use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Traits\Plugins\AcceptsJson;

class ColnectConnector extends Connector
{
    use AcceptsJson;

    /**
     * @var string
     */
    protected string $appId;

    /**
     * @var string
     */
    protected string $appSecret;

    /**
     * @var string
     */
    protected string $language;

    /**
     * @param string $appId
     * @param string $appSecret
     * @param string $language
     *
     * @throws \InvalidArgumentException When inputs would corrupt the request URL.
     */
    public function __construct(string $appId, string $appSecret, string $language = 'en')
    {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $appId) !== 1) {
            throw new \InvalidArgumentException('App ID must contain only letters, digits, underscores or hyphens.');
        }

        if ($appSecret === '') {
            throw new \InvalidArgumentException('App secret must not be empty.');
        }

        if (preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $language) !== 1) {
            throw new \InvalidArgumentException('Language must be a 2-letter code, optionally with a region (e.g. "en" or "en_US").');
        }

        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->language = $language;
    }

    /**
     * The Base URL of the API
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return "https://api.colnect.net/{$this->language}/api/{$this->appId}";
    }

    /**
     * Default headers for every request
     *
     * @return array<string, mixed>
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'ColnectSaloonClient/1.0.0',
        ];
    }

    /**
     * Boot the connector and handle authentication
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function boot(PendingRequest $pendingRequest): void
    {
        $timestamp = (string) time();
        $parsedUrlPath = parse_url($pendingRequest->getUrl(), PHP_URL_PATH);
        $path = is_string($parsedUrlPath) ? $parsedUrlPath : '';
        $hash = hash_hmac('sha256', $path . '>|<' . $timestamp, $this->appSecret);

        $pendingRequest->headers()->add('Capi-Timestamp', $timestamp);
        $pendingRequest->headers()->add('Capi-Hash', $hash);
    }
}
