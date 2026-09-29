<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Support;

use Slimad\ColnectApi\Exceptions\InvalidArgumentException;

/**
 * Immutable collection of the "common list filters" documented by CAPI.
 *
 * Filters are appended to the request path as /key/value pairs, in the order
 * they were added:
 *
 *     Filters::make()->country(5)->year(1980)->toPath(); // /country/5/year/1980
 */
final class Filters
{
    /**
     * @var array<string, string>
     */
    private array $filters = [];

    /**
     * @param array<string, string|int|array<int, string|int>> $filters
     */
    public function __construct(array $filters = [])
    {
        foreach ($filters as $key => $value) {
            $key = Guard::filterKey((string) $key);

            $this->filters[$key] = self::normaliseValue($key, $value);
        }
    }

    /**
     * @param array<string, string|int|array<int, string|int>> $filters
     * @return self
     */
    public static function make(array $filters = []): self
    {
        return new self($filters);
    }

    /**
     * Return a copy of this collection with an extra filter applied.
     *
     * @param string $key
     * @param string|int|array<int, string|int> $value
     * @return self
     */
    public function with(string $key, string|int|array $value): self
    {
        $key = Guard::filterKey($key);

        $clone = clone $this;
        $clone->filters[$key] = self::normaliseValue($key, $value);

        return $clone;
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function country(string|int $value): self
    {
        return $this->with('country', $value);
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function producer(string|int $value): self
    {
        return $this->with('producer', $value);
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function series(string|int $value): self
    {
        return $this->with('series', $value);
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function year(string|int $value): self
    {
        return $this->with('year', $value);
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function theme(string|int $value): self
    {
        return $this->with('theme', $value);
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function catalog(string|int $value): self
    {
        return $this->with('catalog', $value);
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function currency(string|int $value): self
    {
        return $this->with('currency', $value);
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function faceValue(string|int $value): self
    {
        return $this->with('face_value', $value);
    }

    /**
     * @param string|int $value
     * @return self
     */
    public function system(string|int $value): self
    {
        return $this->with('system', $value);
    }

    /**
     * Sort option, see the category_sort_options action for the available names.
     *
     * @param string $sort
     * @return self
     */
    public function sort(string $sort): self
    {
        return $this->with('sort', $sort);
    }

    /**
     * @param int $page
     * @return self
     */
    public function page(int $page): self
    {
        return $this->with('page', Guard::positiveInt($page, 'Page'));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->filters);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->filters === [];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->filters;
    }

    /**
     * Render the filters as path segments, ready to be appended to an endpoint.
     *
     * @return string
     */
    public function toPath(): string
    {
        $path = '';

        foreach ($this->filters as $key => $value) {
            $path .= '/' . $key . '/' . rawurlencode($value);
        }

        return $path;
    }

    /**
     * @param string $key
     * @param string|int|array<int, string|int> $value
     * @return string
     */
    private static function normaliseValue(string $key, string|int|array $value): string
    {
        if (is_array($value)) {
            if ($value === []) {
                throw new InvalidArgumentException('Filter "' . $key . '" must not be an empty list.');
            }

            $value = implode(',', array_map(
                static fn (string|int $item): string => (string) $item,
                $value
            ));
        }

        return Guard::printable((string) $value, 'Filter "' . $key . '"');
    }
}
