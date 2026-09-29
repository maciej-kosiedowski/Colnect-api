<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Support;

use PHPUnit\Framework\TestCase;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Support\Guard;

class GuardTest extends TestCase
{
    public function test_it_returns_the_value_of_a_category_enum(): void
    {
        $this->assertSame('stamps', Guard::category(CategoryType::Stamps));
    }

    public function test_it_accepts_a_string_category(): void
    {
        $this->assertSame('bank_cards', Guard::category('bank_cards'));
    }

    public function test_it_rejects_a_malformed_string_category(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Category must contain only lowercase letters and underscores.');

        Guard::category('Bank Cards');
    }

    public function test_it_rejects_a_slug_with_digits(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Guard::slug('coins2', 'Category');
    }

    public function test_it_accepts_filter_keys_with_digits_and_underscores(): void
    {
        $this->assertSame('face_value2', Guard::filterKey('face_value2'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidFilterKeyProvider(): array
    {
        return [
            'empty' => [''],
            'leading digit' => ['2face'],
            'leading underscore' => ['_face'],
            'uppercase' => ['Face'],
            'dash' => ['face-value'],
            'newline' => ["face\n"],
        ];
    }

    /**
     * @dataProvider invalidFilterKeyProvider
     */
    public function test_it_rejects_invalid_filter_keys(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);

        Guard::filterKey($key);
    }

    public function test_it_accepts_a_non_empty_value(): void
    {
        $this->assertSame('blue', Guard::nonEmpty('blue', 'Search query'));
    }

    public function test_it_rejects_an_empty_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Search query must not be empty.');

        Guard::nonEmpty('', 'Search query');
    }

    public function test_it_accepts_a_printable_value(): void
    {
        $this->assertSame('MyApp/1.0', Guard::printable('MyApp/1.0', 'User agent'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function controlCharacterProvider(): array
    {
        return [
            'null byte' => ["My\0App"],
            'newline' => ["My\nApp"],
            'carriage return' => ["My\rApp"],
            'delete' => ["My\x7FApp"],
        ];
    }

    /**
     * @dataProvider controlCharacterProvider
     */
    public function test_it_rejects_control_characters(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User agent must not contain control characters.');

        Guard::printable($value, 'User agent');
    }

    public function test_it_rejects_an_empty_printable_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User agent must not be empty.');

        Guard::printable('', 'User agent');
    }

    public function test_it_accepts_a_collector_name(): void
    {
        $this->assertSame('some.collector-1', Guard::collector('some.collector-1'));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidCollectorProvider(): array
    {
        return [
            'space' => ['some collector'],
            'slash' => ['some/collector'],
            'backslash' => ['some\\collector'],
            'question mark' => ['some?collector'],
            'hash' => ['some#collector'],
            'tab' => ["some\tcollector"],
        ];
    }

    /**
     * @dataProvider invalidCollectorProvider
     */
    public function test_it_rejects_invalid_collector_names(string $collector): void
    {
        $this->expectException(InvalidArgumentException::class);

        Guard::collector($collector);
    }

    public function test_it_rejects_an_empty_collector_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Collector name must not be empty.');

        Guard::collector('');
    }

    public function test_it_accepts_a_positive_integer(): void
    {
        $this->assertSame(1, Guard::positiveInt(1, 'Page'));
    }

    public function test_it_rejects_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be a positive integer.');

        Guard::positiveInt(0, 'Page');
    }

    public function test_it_accepts_the_bounds_of_a_range(): void
    {
        $this->assertSame(1, Guard::between(1, 1, 20, 'Max sales'));
        $this->assertSame(20, Guard::between(20, 1, 20, 'Max sales'));
    }

    public function test_it_rejects_a_value_below_the_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Max sales must be between 1 and 20.');

        Guard::between(0, 1, 20, 'Max sales');
    }

    public function test_it_rejects_a_value_above_the_range(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Guard::between(21, 1, 20, 'Max sales');
    }

    public function test_it_normalises_a_single_integer_id(): void
    {
        $this->assertSame('5', Guard::idList(5, 'Item IDs'));
    }

    public function test_it_normalises_a_comma_separated_string_of_ids(): void
    {
        $this->assertSame('5,7,469', Guard::idList('5, 7 ,469', 'Item IDs'));
    }

    public function test_it_normalises_a_list_of_ids(): void
    {
        $this->assertSame('5,7', Guard::idList([5, '7'], 'Item IDs'));
    }

    public function test_it_rejects_non_numeric_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item IDs must contain only numeric IDs.');

        Guard::idList('5,abc', 'Item IDs');
    }

    public function test_it_rejects_an_empty_id_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Guard::idList('', 'Item IDs');
    }

    public function test_it_rejects_zero_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item IDs must contain only positive IDs.');

        Guard::idList('0', 'Item IDs');
    }

    public function test_it_rejects_an_empty_list_of_ids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item IDs must contain at least one ID.');

        Guard::idList([], 'Item IDs');
    }

    public function test_it_accepts_the_maximum_number_of_ids(): void
    {
        $this->assertSame('1,2', Guard::idList([1, 2], 'Item IDs', 2));
    }

    public function test_it_rejects_more_ids_than_the_maximum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item IDs must not contain more than 2 IDs.');

        Guard::idList([1, 2, 3], 'Item IDs', 2);
    }
}
