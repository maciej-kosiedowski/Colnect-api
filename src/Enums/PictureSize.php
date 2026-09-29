<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Enums;

/**
 * The two picture variants served by Colnect's image host.
 */
enum PictureSize: string
{
    case Thumbnail = 't';
    case Full = 'f';
}
