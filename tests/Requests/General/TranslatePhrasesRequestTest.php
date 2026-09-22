<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Requests\General;

use PHPUnit\Framework\TestCase;
use Saloon\Enums\Method;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Requests\General\TranslatePhrasesRequest;

class TranslatePhrasesRequestTest extends TestCase
{
    public function test_it_posts_to_the_translate_phrases_endpoint(): void
    {
        $request = new TranslatePhrasesRequest(['Mint', 'Used']);

        $this->assertEquals(Method::POST, $request->getMethod());
        $this->assertSame('/translate_phrases', $request->resolveEndpoint());
    }

    public function test_it_sends_the_phrases_as_a_json_array(): void
    {
        $request = new TranslatePhrasesRequest(['Mint', 'Used']);

        $this->assertSame('["Mint","Used"]', $request->body()->get('phrases'));
    }

    public function test_it_reindexes_the_phrases(): void
    {
        $request = new TranslatePhrasesRequest([3 => 'Mint', 7 => 'Used']);

        $this->assertSame('["Mint","Used"]', $request->body()->get('phrases'));
    }

    public function test_it_keeps_unicode_and_slashes_readable(): void
    {
        $request = new TranslatePhrasesRequest(['Poczta Polska / Łódź']);

        $this->assertSame('["Poczta Polska / Łódź"]', $request->body()->get('phrases'));
    }

    public function test_it_rejects_an_empty_list(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Phrases must not be empty.');

        new TranslatePhrasesRequest([]);
    }

    public function test_it_rejects_an_empty_phrase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Phrase must not be empty.');

        new TranslatePhrasesRequest(['Mint', '']);
    }
}
