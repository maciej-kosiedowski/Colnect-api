<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Support;

use PHPUnit\Framework\TestCase;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Support\Filters;

class FiltersTest extends TestCase
{
    public function test_it_starts_empty(): void
    {
        $filters = new Filters();

        $this->assertTrue($filters->isEmpty());
        $this->assertSame('', $filters->toPath());
        $this->assertSame([], $filters->toArray());
    }

    public function test_it_is_built_from_an_array(): void
    {
        $filters = Filters::make(['country' => 5, 'year' => '1980']);

        $this->assertFalse($filters->isEmpty());
        $this->assertSame(['country' => '5', 'year' => '1980'], $filters->toArray());
        $this->assertSame('/country/5/year/1980', $filters->toPath());
    }

    public function test_it_keeps_the_order_filters_were_added_in(): void
    {
        $filters = Filters::make()->year(1980)->country(5);

        $this->assertSame('/year/1980/country/5', $filters->toPath());
    }

    public function test_it_is_immutable(): void
    {
        $filters = Filters::make()->country(5);
        $other = $filters->year(1980);

        $this->assertSame('/country/5', $filters->toPath());
        $this->assertSame('/country/5/year/1980', $other->toPath());
        $this->assertNotSame($filters, $other);
    }

    public function test_it_overwrites_a_filter_with_the_same_key(): void
    {
        $filters = Filters::make()->country(5)->country(7);

        $this->assertSame('/country/7', $filters->toPath());
    }

    public function test_it_exposes_every_documented_filter(): void
    {
        $filters = Filters::make()
            ->country(1)
            ->producer(2)
            ->series(3)
            ->year(1980)
            ->theme(4)
            ->catalog(5)
            ->currency(6)
            ->faceValue(7)
            ->system(8)
            ->sort('name')
            ->page(2);

        $this->assertSame(
            '/country/1/producer/2/series/3/year/1980/theme/4/catalog/5/currency/6/face_value/7/system/8/sort/name/page/2',
            $filters->toPath()
        );
    }

    public function test_it_supports_undocumented_filters(): void
    {
        $filters = Filters::make()->with('hidden_filter', 'value');

        $this->assertTrue($filters->has('hidden_filter'));
        $this->assertFalse($filters->has('country'));
        $this->assertSame('/hidden_filter/value', $filters->toPath());
    }

    public function test_it_joins_list_values_with_commas(): void
    {
        $filters = Filters::make()->with('theme', [3, '4']);

        $this->assertSame('/theme/3%2C4', $filters->toPath());
        $this->assertSame(['theme' => '3,4'], $filters->toArray());
    }

    public function test_it_url_encodes_filter_values(): void
    {
        $filters = Filters::make()->with('name', 'blue & red');

        $this->assertSame('/name/blue%20%26%20red', $filters->toPath());
    }

    public function test_it_url_encodes_path_traversal_attempts(): void
    {
        $filters = Filters::make()->country('../../admin');

        $this->assertStringNotContainsString('../', $filters->toPath());
        $this->assertSame('/country/..%2F..%2Fadmin', $filters->toPath());
    }

    public function test_it_rejects_an_invalid_filter_key_in_the_constructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Filters::make(['Country' => 5]);
    }

    public function test_it_rejects_an_invalid_filter_key(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Filters::make()->with('bad key', 'value');
    }

    public function test_it_rejects_an_empty_filter_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter "country" must not be empty.');

        Filters::make()->country('');
    }

    public function test_it_rejects_an_empty_list_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter "theme" must not be an empty list.');

        Filters::make()->with('theme', []);
    }

    public function test_it_rejects_a_filter_value_with_control_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Filters::make()->sort("name\n");
    }

    public function test_it_rejects_a_non_positive_page(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be a positive integer.');

        Filters::make()->page(0);
    }
}
