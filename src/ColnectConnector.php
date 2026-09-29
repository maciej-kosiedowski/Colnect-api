<?php

declare(strict_types=1);

namespace Slimad\ColnectApi;

use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Traits\Plugins\AcceptsJson;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Support\Guard;

class ColnectConnector extends Connector
{
    use AcceptsJson;

    /**
     * Separator used when building the request hash.
     */
    public const HASH_SEPARATOR = '>|<';

    /**
     * Colnect asks for a user agent clearly identifying the calling
     * application, of at least this many characters.
     */
    public const MIN_USER_AGENT_LENGTH = 16;

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
     * @var string
     */
    protected string $userAgent;

    /**
     * @param string $appId
     * @param string $appSecret
     * @param string $language
     * @param string $userAgent String identifying your application, such as MyCoolApp/1.2.3.4
     *
     * @throws InvalidArgumentException When inputs would corrupt the request URL or headers.
     */
    public function __construct(
        string $appId,
        string $appSecret,
        string $language = 'en',
        string $userAgent = 'ColnectSaloonClient/1.0.0'
    ) {
        if (preg_match('/^[A-Za-z0-9_-]+$/D', $appId) !== 1) {
            throw new InvalidArgumentException('App ID must contain only letters, digits, underscores or hyphens.');
        }

        if ($appSecret === '') {
            throw new InvalidArgumentException('App secret must not be empty.');
        }

        if (preg_match('/^[a-z]{2}(_[A-Z]{2})?$/D', $language) !== 1) {
            throw new InvalidArgumentException('Language must be a 2-letter code, optionally with a region (e.g. "en" or "en_US").');
        }

        Guard::printable($userAgent, 'User agent');

        if (strlen($userAgent) < self::MIN_USER_AGENT_LENGTH) {
            throw new InvalidArgumentException('User agent must be at least ' . self::MIN_USER_AGENT_LENGTH . ' characters long and identify your application.');
        }

        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->language = $language;
        $this->userAgent = $userAgent;
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
     * The language every response is translated into.
     *
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * The application ID sent as part of every URL.
     *
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
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
            'User-Agent' => $this->userAgent,
        ];
    }

    /**
     * Default HTTP client config
     *
     * CAPI is served exclusively over HTTP/2 and rejects HTTP/1.x requests
     * with a 426 Upgrade Required response.
     *
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        return [
            'version' => 2.0,
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
        $parsedPath = parse_url($pendingRequest->getUrl(), PHP_URL_PATH);
        $path = is_string($parsedPath) ? $parsedPath : '';
        $hash = hash_hmac('sha256', $path . self::HASH_SEPARATOR . $timestamp, $this->appSecret);

        $pendingRequest->headers()->add('Capi-Timestamp', $timestamp);
        $pendingRequest->headers()->add('Capi-Hash', $hash);
    }
}
