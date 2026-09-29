<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Support;

use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;

/**
 * Input validation helpers shared by every request class.
 *
 * Every CAPI parameter ends up inside the request path, which is also the
 * string that gets signed, so malformed input must never reach the wire.
 */
final class Guard
{
    private function __construct()
    {
    }

    /**
     * Validate a category and return its string value.
     *
     * @param CategoryType|string $category
     * @return string
     */
    public static function category(CategoryType|string $category): string
    {
        if ($category instanceof CategoryType) {
            return $category->value;
        }

        return self::slug($category, 'Category');
    }

    /**
     * Validate a lowercase, underscore separated value such as a category name.
     *
     * @param string $value
     * @param string $label
     * @return string
     */
    public static function slug(string $value, string $label): string
    {
        if (preg_match('/^[a-z_]+$/D', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must contain only lowercase letters and underscores.');
        }

        return $value;
    }

    /**
     * Validate a filter key, for example "country" or "face_value".
     *
     * @param string $key
     * @return string
     */
    public static function filterKey(string $key): string
    {
        if (preg_match('/^[a-z][a-z0-9_]*$/D', $key) !== 1) {
            throw new InvalidArgumentException('Filter key "' . $key . '" must start with a lowercase letter and contain only lowercase letters, digits or underscores.');
        }

        return $key;
    }

    /**
     * Validate that a value is not an empty string.
     *
     * @param string $value
     * @param string $label
     * @return string
     */
    public static function nonEmpty(string $value, string $label): string
    {
        if ($value === '') {
            throw new InvalidArgumentException($label . ' must not be empty.');
        }

        return $value;
    }

    /**
     * Validate that a value carries no control characters, which would allow
     * header or path injection.
     *
     * @param string $value
     * @param string $label
     * @return string
     */
    public static function printable(string $value, string $label): string
    {
        self::nonEmpty($value, $label);

        if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidArgumentException($label . ' must not contain control characters.');
        }

        return $value;
    }

    /**
     * Validate a Colnect collector (user) name.
     *
     * @param string $collector
     * @return string
     */
    public static function collector(string $collector): string
    {
        self::printable($collector, 'Collector name');

        if (preg_match('#[\s/\\\\?\#]#', $collector) === 1) {
            throw new InvalidArgumentException('Collector name must not contain whitespace or URL delimiters.');
        }

        return $collector;
    }

    /**
     * Validate a positive integer.
     *
     * @param int $value
     * @param string $label
     * @return int
     */
    public static function positiveInt(int $value, string $label): int
    {
        if ($value < 1) {
            throw new InvalidArgumentException($label . ' must be a positive integer.');
        }

        return $value;
    }

    /**
     * Validate an integer within an inclusive range.
     *
     * @param int $value
     * @param int $min
     * @param int $max
     * @param string $label
     * @return int
     */
    public static function between(int $value, int $min, int $max, string $label): int
    {
        if ($value < $min || $value > $max) {
            throw new InvalidArgumentException($label . ' must be between ' . $min . ' and ' . $max . '.');
        }

        return $value;
    }

    /**
     * Normalise a list of numeric IDs into the comma separated form CAPI expects.
     *
     * @param int|string|array<int, int|string> $ids A single ID, a comma separated string or a list of IDs.
     * @param string $label
     * @param int|null $max Maximum number of IDs accepted by the endpoint.
     * @return string
     */
    public static function idList(int|string|array $ids, string $label, ?int $max = null): string
    {
        $candidates = is_array($ids)
            ? $ids
            : explode(',', (string) $ids);

        $normalised = [];

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);

            if (preg_match('/^[0-9]+$/D', $candidate) !== 1) {
                throw new InvalidArgumentException($label . ' must contain only numeric IDs.');
            }

            $id = (int) $candidate;

            if ($id < 1) {
                throw new InvalidArgumentException($label . ' must contain only positive IDs.');
            }

            $normalised[] = (string) $id;
        }

        if ($normalised === []) {
            throw new InvalidArgumentException($label . ' must contain at least one ID.');
        }

        if ($max !== null && count($normalised) > $max) {
            throw new InvalidArgumentException($label . ' must not contain more than ' . $max . ' IDs.');
        }

        return implode(',', $normalised);
    }
}
