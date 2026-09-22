<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Support;

use Slimad\ColnectApi\Enums\PictureSize;

/**
 * Builds the image URLs described in the CAPI specification from the picture
 * IDs returned by the list, item and search actions.
 */
final class PictureUrl
{
    /**
     * Host serving every Colnect picture.
     */
    public const HOST = 'https://i.colnect.net';

    /**
     * Used when an item name urlizes to an empty string.
     */
    public const FALLBACK_NAME = 'item';

    private function __construct()
    {
    }

    /**
     * Build the URL of an item picture.
     *
     * The picture ID is split on its thousands separator, so 142891 becomes
     * 142/891 and 1234567 becomes 1234/567.
     *
     * @param int $pictureId Value of front_picture_id or back_picture_id.
     * @param string $itemName Value of item_name, slugged with Str::urlize().
     * @param PictureSize $size Thumbnail (/t/) or full size (/f/) picture.
     * @param string $extension Image extension, jpg unless stated otherwise.
     * @return string
     */
    public static function item(
        int $pictureId,
        string $itemName,
        PictureSize $size = PictureSize::Thumbnail,
        string $extension = 'jpg'
    ): string {
        Guard::positiveInt($pictureId, 'Picture ID');
        Guard::slug($extension, 'Picture extension');

        $name = Str::urlize($itemName);

        if ($name === '') {
            $name = self::FALLBACK_NAME;
        }

        $path = intdiv($pictureId, 1000) . '/' . str_pad((string) ($pictureId % 1000), 3, '0', STR_PAD_LEFT);

        return self::HOST . '/images/' . $size->value . '/' . $path . '/' . rawurlencode($name) . '.' . $extension;
    }

    /**
     * Build the URL of a country flag from the ID returned by country_flag.
     *
     * @param int $flagId
     * @param int $size Width of the flag in pixels, as served by Colnect.
     * @return string
     */
    public static function flag(int $flagId, int $size = 32): string
    {
        Guard::positiveInt($flagId, 'Flag ID');
        Guard::positiveInt($size, 'Flag size');

        return self::HOST . '/flag/' . $size . '/' . $flagId . '-country.png';
    }
}
