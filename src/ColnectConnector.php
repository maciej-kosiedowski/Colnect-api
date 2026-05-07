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
     */
    public function __construct(string $appId, string $appSecret, string $language = 'en')
    {
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
        $path = $pendingRequest->getUri()->getPath();
        $hash = hash_hmac('sha256', $path . '>|<' . $timestamp, $this->appSecret);

        $pendingRequest->headers()->add('Capi-Timestamp', $timestamp);
        $pendingRequest->headers()->add('Capi-Hash', $hash);
    }
}
