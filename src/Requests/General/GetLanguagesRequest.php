<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\General;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetLanguagesRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/languages';
    }
}
