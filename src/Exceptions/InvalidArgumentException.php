<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Exceptions;

/**
 * Thrown whenever user supplied input would produce an invalid CAPI request.
 *
 * It extends the SPL exception so existing catch blocks keep working.
 */
class InvalidArgumentException extends \InvalidArgumentException
{
}
