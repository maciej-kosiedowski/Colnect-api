<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Support;

use PHPUnit\Framework\TestCase;
use Slimad\ColnectApi\Support\Str;

class StrTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function urlizeProvider(): array
    {
        return [
            'plain name' => ['Some Name', 'Some_Name'],
            'space sequences collapse' => ["Some   \t Name", 'Some_Name'],
            'underscore sequences collapse' => ['Some___Name', 'Some_Name'],
            'invalid characters are stripped' => ['Pasteur, Louis (1822-1895)', 'Pasteur_Louis_1822-1895'],
            'quotes and slashes are stripped' => ['A/B "C" #1', 'AB_C_1'],
            'html entities are decoded' => ['Caf&eacute; &amp; Bar', 'Café_Bar'],
            'unknown entities become underscores' => ['A&nonexistent;B', 'A_B'],
            'leading and trailing underscores are trimmed' => ['  .Name.  ', 'Name'],
            'empty string stays empty' => ['', ''],
            'only invalid characters' => ['.,;', ''],
        ];
    }

    /**
     * @dataProvider urlizeProvider
     */
    public function test_it_urlizes_names(string $input, string $expected): void
    {
        $this->assertSame($expected, Str::urlize($input));
    }
}
