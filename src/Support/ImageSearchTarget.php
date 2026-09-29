<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Support;

use Saloon\Data\MultipartValue;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;

/**
 * The picture (or pair of pictures) submitted to the image_search action.
 *
 * A target always carries a front picture and may carry a back picture, which
 * Colnect currently supports for coins only:
 *
 *     ImageSearchTarget::fromUrl('https://example.com/front.jpg')
 *         ->withBackUrl('https://example.com/back.jpg');
 */
final class ImageSearchTarget
{
    /**
     * @param array<string, array{value: string, filename: string|null}> $parts
     */
    private function __construct(private array $parts)
    {
    }

    /**
     * Search by the URL of an image on the internet.
     */
    public static function fromUrl(string $url): self
    {
        return new self(['picture_url' => self::url($url)]);
    }

    /**
     * Search by base64 encoded image data.
     */
    public static function fromBase64(string $data): self
    {
        return new self(['picture_base64' => self::base64($data)]);
    }

    /**
     * Search by an image read from the local disk.
     */
    public static function fromFile(string $path): self
    {
        return new self(['picture_upload' => self::file($path)]);
    }

    /**
     * Add the URL of the back picture (coins only).
     */
    public function withBackUrl(string $url): self
    {
        return $this->withBack('picture_url_back', self::url($url));
    }

    /**
     * Add base64 encoded data of the back picture (coins only).
     */
    public function withBackBase64(string $data): self
    {
        return $this->withBack('picture_base64_back', self::base64($data));
    }

    /**
     * Add the back picture read from the local disk (coins only).
     */
    public function withBackFile(string $path): self
    {
        return $this->withBack('picture_upload_back', self::file($path));
    }

    /**
     * Whether a back picture was added to the target.
     */
    public function hasBackPicture(): bool
    {
        foreach (array_keys($this->parts) as $name) {
            if (str_ends_with($name, '_back')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render the target as the multipart body of the image_search request.
     *
     * @return array<int, MultipartValue>
     */
    public function toMultipart(): array
    {
        $body = [];

        foreach ($this->parts as $name => $part) {
            $body[] = new MultipartValue($name, $part['value'], $part['filename']);
        }

        return $body;
    }

    /**
     * @param array{value: string, filename: string|null} $part
     */
    private function withBack(string $name, array $part): self
    {
        if ($this->hasBackPicture()) {
            throw new InvalidArgumentException('Only one back picture may be given.');
        }

        $clone = clone $this;
        $clone->parts[$name] = $part;

        return $clone;
    }

    /**
     * @return array{value: string, filename: string|null}
     */
    private static function url(string $url): array
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException('Picture URL must be a valid URL.');
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvalidArgumentException('Picture URL must use the http or https scheme.');
        }

        return ['value' => $url, 'filename' => null];
    }

    /**
     * @return array{value: string, filename: string|null}
     */
    private static function base64(string $data): array
    {
        Guard::nonEmpty($data, 'Picture data');

        if (base64_decode($data, true) === false) {
            throw new InvalidArgumentException('Picture data must be valid base64.');
        }

        return ['value' => $data, 'filename' => null];
    }

    /**
     * @return array{value: string, filename: string|null}
     */
    private static function file(string $path): array
    {
        Guard::nonEmpty($path, 'Picture path');

        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidArgumentException('Picture file "' . $path . '" does not exist or is not readable.');
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new InvalidArgumentException('Picture file "' . $path . '" could not be read.');
        }

        return ['value' => $contents, 'filename' => basename($path)];
    }
}
