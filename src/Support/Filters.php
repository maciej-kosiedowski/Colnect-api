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
     */
    public static function make(array $filters = []): self
    {
        return new self($filters);
    }

    /**
     * Return a copy of this collection with an extra filter applied.
     *
     * @param string|int|array<int, string|int> $value
     */
    public function with(string $key, string|int|array $value): self
    {
        $key = Guard::filterKey($key);

        $clone = clone $this;
        $clone->filters[$key] = self::normaliseValue($key, $value);

        return $clone;
    }

    public function country(string|int $value): self
    {
        return $this->with('country', $value);
    }

    public function producer(string|int $value): self
    {
        return $this->with('producer', $value);
    }

    public function series(string|int $value): self
    {
        return $this->with('series', $value);
    }

    public function year(string|int $value): self
    {
        return $this->with('year', $value);
    }

    public function theme(string|int $value): self
    {
        return $this->with('theme', $value);
    }

    public function catalog(string|int $value): self
    {
        return $this->with('catalog', $value);
    }

    public function currency(string|int $value): self
    {
        return $this->with('currency', $value);
    }

    public function faceValue(string|int $value): self
    {
        return $this->with('face_value', $value);
    }

    public function system(string|int $value): self
    {
        return $this->with('system', $value);
    }

    /**
     * Sort option, see the category_sort_options action for the available names.
     */
    public function sort(string $sort): self
    {
        return $this->with('sort', $sort);
    }

    public function page(int $page): self
    {
        return $this->with('page', Guard::positiveInt($page, 'Page'));
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->filters);
    }

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
     * @param string|int|array<int, string|int> $value
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
