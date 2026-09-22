<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Support;

use PHPUnit\Framework\TestCase;
use Slimad\ColnectApi\Enums\PictureSize;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Support\PictureUrl;

class PictureUrlTest extends TestCase
{
    public function test_it_builds_a_thumbnail_url_by_default(): void
    {
        $this->assertSame(
            'https://i.colnect.net/images/t/142/891/Pasteur-Louis.jpg',
            PictureUrl::item(142891, 'Pasteur-Louis')
        );
    }

    public function test_it_builds_a_full_size_url(): void
    {
        $this->assertSame(
            'https://i.colnect.net/images/f/1234/567/Some_Name.jpg',
            PictureUrl::item(1234567, 'Some Name', PictureSize::Full)
        );
    }

    public function test_it_pads_the_last_three_digits(): void
    {
        $this->assertSame(
            'https://i.colnect.net/images/t/142/007/Name.jpg',
            PictureUrl::item(142007, 'Name')
        );
    }

    public function test_it_urlizes_the_item_name(): void
    {
        $this->assertSame(
            'https://i.colnect.net/images/t/1/000/Some_Name.jpg',
            PictureUrl::item(1000, 'Some. Name!')
        );
    }

    public function test_it_url_encodes_the_item_name(): void
    {
        $this->assertSame(
            'https://i.colnect.net/images/t/1/001/Caf%C3%A9.jpg',
            PictureUrl::item(1001, 'Café')
        );
    }

    public function test_it_falls_back_when_the_name_urlizes_to_nothing(): void
    {
        $this->assertSame(
            'https://i.colnect.net/images/t/1/002/item.jpg',
            PictureUrl::item(1002, '...')
        );
    }

    public function test_it_accepts_another_extension(): void
    {
        $this->assertSame(
            'https://i.colnect.net/images/t/1/003/Name.png',
            PictureUrl::item(1003, 'Name', PictureSize::Thumbnail, 'png')
        );
    }

    public function test_it_rejects_a_non_positive_picture_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Picture ID must be a positive integer.');

        PictureUrl::item(0, 'Name');
    }

    public function test_it_rejects_a_malformed_extension(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PictureUrl::item(1, 'Name', PictureSize::Thumbnail, '../evil');
    }

    public function test_it_builds_a_flag_url(): void
    {
        $this->assertSame('https://i.colnect.net/flag/32/469-country.png', PictureUrl::flag(469));
    }

    public function test_it_builds_a_flag_url_of_another_size(): void
    {
        $this->assertSame('https://i.colnect.net/flag/64/469-country.png', PictureUrl::flag(469, 64));
    }

    public function test_it_rejects_a_non_positive_flag_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Flag ID must be a positive integer.');

        PictureUrl::flag(0);
    }

    public function test_it_rejects_a_non_positive_flag_size(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Flag size must be a positive integer.');

        PictureUrl::flag(469, 0);
    }
}
