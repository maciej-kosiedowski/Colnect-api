<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Requests\General;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Support\Guard;

/**
 * Translations of phrases that already exist on Colnect, into the language of
 * the connector.
 *
 * POST /translate_phrases with a JSON encoded "phrases" field. POST is used to
 * avoid the URL length limit of the GET variant.
 *
 * Returns an array of translated phrases.
 */
class TranslatePhrasesRequest extends Request implements HasBody
{
    use HasFormBody;

    protected Method $method = Method::POST;

    /**
     * @var array<int, string>
     */
    protected array $phrases;

    /**
     * @param array<int, string> $phrases Phrases to translate.
     *
     * @throws \Slimad\ColnectApi\Exceptions\InvalidArgumentException When the list is empty or holds an empty phrase.
     */
    public function __construct(array $phrases)
    {
        if ($phrases === []) {
            throw new InvalidArgumentException('Phrases must not be empty.');
        }

        foreach ($phrases as $phrase) {
            Guard::nonEmpty($phrase, 'Phrase');
        }

        $this->phrases = array_values($phrases);
    }

    public function resolveEndpoint(): string
    {
        return '/translate_phrases';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'phrases' => json_encode($this->phrases, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }
}
