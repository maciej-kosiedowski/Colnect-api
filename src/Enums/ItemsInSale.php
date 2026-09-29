<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Enums;

/**
 * The items_in_sale filter accepted by the item_sales action.
 */
enum ItemsInSale: string
{
    case Single = 'single';
    case Lots = 'lots';
}
