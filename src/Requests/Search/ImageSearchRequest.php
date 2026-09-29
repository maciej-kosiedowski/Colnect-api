<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\Search;

use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Support\Filters;
use Slimad\ColnectApi\Support\Guard;
use Slimad\ColnectApi\Support\ImageSearchTarget;

/**
 * Search catalog items by their pictures.
 *
 * POST /image_search[/cat/{category}]/{filters}
 *
 * Image search is disabled by default and is billed per call, so it has to be
 * enabled for the application first. Because image recognition is imperfect,
 * narrowing the search down with a category and filters is recommended.
 *
 * Returns {"success": bool, "code": string, "data": {"distances": [...],
 * "best_matches": [...]}} where c_id is the item ID and p_id the picture ID.
 */
class ImageSearchRequest extends Request implements HasBody
{
    use HasMultipartBody;

    /**
     * The HTTP method of the request
     *
     * @var Method
     */
    protected Method $method = Method::POST;

    /**
     * @var ImageSearchTarget
     */
    protected ImageSearchTarget $target;

    /**
     * @var string|null
     */
    protected ?string $category;

    /**
     * @var Filters
     */
    protected Filters $filters;

    /**
     * @param ImageSearchTarget $target The picture, or pair of pictures, to look for.
     * @param CategoryType|string|null $category Category to search in. Null searches every category.
     * @param Filters|null $filters Common list filters narrowing the search down.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When a string category contains unexpected characters.
     */
    public function __construct(
        ImageSearchTarget $target,
        CategoryType|string|null $category = null,
        ?Filters $filters = null
    ) {
        $this->target = $target;
        $this->category = $category === null ? null : Guard::category($category);
        $this->filters = $filters ?? new Filters();
    }

    /**
     * The endpoint for the request
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        $endpoint = '/image_search';

        if ($this->category !== null) {
            $endpoint .= '/cat/' . rawurlencode($this->category);
        }

        return $endpoint . $this->filters->toPath();
    }

    /**
     * Default body
     *
     * @return array<int, MultipartValue>
     */
    protected function defaultBody(): array
    {
        return $this->target->toMultipart();
    }
}
