<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\Search;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Enums\CategoryType;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\Search\ImageSearchRequest;
use Slimad\ColnectApi\Support\Filters;
use Slimad\ColnectApi\Support\ImageSearchTarget;

class ImageSearchRequestTest extends TestCase
{
    public function test_it_posts_to_the_image_search_endpoint(): void
    {
        $request = new ImageSearchRequest(ImageSearchTarget::fromUrl('https://example.com/front.jpg'));

        $this->assertEquals(Method::POST, $request->getMethod());
        $this->assertSame('/image_search', $request->resolveEndpoint());
    }

    public function test_it_narrows_the_search_to_a_category(): void
    {
        $request = new ImageSearchRequest(
            ImageSearchTarget::fromUrl('https://example.com/front.jpg'),
            CategoryType::Coins
        );

        $this->assertSame('/image_search/cat/coins', $request->resolveEndpoint());
    }

    public function test_it_narrows_the_search_with_filters(): void
    {
        $request = new ImageSearchRequest(
            ImageSearchTarget::fromUrl('https://example.com/front.jpg'),
            'coins',
            Filters::make()->country(5)->year(1980)
        );

        $this->assertSame('/image_search/cat/coins/country/5/year/1980', $request->resolveEndpoint());
    }

    public function test_it_applies_filters_without_a_category(): void
    {
        $request = new ImageSearchRequest(
            ImageSearchTarget::fromUrl('https://example.com/front.jpg'),
            null,
            Filters::make()->country(5)
        );

        $this->assertSame('/image_search/country/5', $request->resolveEndpoint());
    }

    public function test_it_sends_the_target_as_multipart_body(): void
    {
        $request = new ImageSearchRequest(
            ImageSearchTarget::fromUrl('https://example.com/front.jpg')
                ->withBackUrl('https://example.com/back.jpg')
        );

        $body = $request->body()->all();

        $this->assertCount(2, $body);
        $this->assertSame('picture_url', $body[0]->name);
        $this->assertSame('picture_url_back', $body[1]->name);
    }

    public function test_it_rejects_a_malformed_category(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ImageSearchRequest(ImageSearchTarget::fromUrl('https://example.com/front.jpg'), '../admin');
    }
}
