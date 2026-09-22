<?php

declare(strict_types=1);

namespace Slimad\ColnectApi\Tests\Support;

use PHPUnit\Framework\TestCase;
use Slimad\ColnectApi\Exceptions\InvalidArgumentException;
use Slimad\ColnectApi\Support\ImageSearchTarget;

class ImageSearchTargetTest extends TestCase
{
    /**
     * @var string
     */
    private string $file = '';

    protected function tearDown(): void
    {
        if ($this->file !== '' && is_file($this->file)) {
            unlink($this->file);
        }

        parent::tearDown();
    }

    public function test_it_builds_a_url_target(): void
    {
        $parts = ImageSearchTarget::fromUrl('https://example.com/front.jpg')->toMultipart();

        $this->assertCount(1, $parts);
        $this->assertSame('picture_url', $parts[0]->name);
        $this->assertSame('https://example.com/front.jpg', $parts[0]->value);
        $this->assertNull($parts[0]->filename);
    }

    public function test_it_builds_a_base64_target(): void
    {
        $parts = ImageSearchTarget::fromBase64(base64_encode('image-bytes'))->toMultipart();

        $this->assertSame('picture_base64', $parts[0]->name);
        $this->assertSame(base64_encode('image-bytes'), $parts[0]->value);
    }

    public function test_it_builds_a_file_target(): void
    {
        $parts = ImageSearchTarget::fromFile($this->createPictureFile())->toMultipart();

        $this->assertSame('picture_upload', $parts[0]->name);
        $this->assertSame('image-bytes', $parts[0]->value);
        $this->assertSame(basename($this->file), $parts[0]->filename);
    }

    public function test_it_adds_a_back_picture_url(): void
    {
        $target = ImageSearchTarget::fromUrl('https://example.com/front.jpg')
            ->withBackUrl('https://example.com/back.jpg');

        $parts = $target->toMultipart();

        $this->assertTrue($target->hasBackPicture());
        $this->assertCount(2, $parts);
        $this->assertSame('picture_url_back', $parts[1]->name);
        $this->assertSame('https://example.com/back.jpg', $parts[1]->value);
    }

    public function test_it_adds_a_back_picture_from_base64(): void
    {
        $parts = ImageSearchTarget::fromUrl('https://example.com/front.jpg')
            ->withBackBase64(base64_encode('back-bytes'))
            ->toMultipart();

        $this->assertSame('picture_base64_back', $parts[1]->name);
    }

    public function test_it_adds_a_back_picture_from_a_file(): void
    {
        $parts = ImageSearchTarget::fromUrl('https://example.com/front.jpg')
            ->withBackFile($this->createPictureFile())
            ->toMultipart();

        $this->assertSame('picture_upload_back', $parts[1]->name);
        $this->assertSame(basename($this->file), $parts[1]->filename);
    }

    public function test_a_front_only_target_has_no_back_picture(): void
    {
        $this->assertFalse(ImageSearchTarget::fromUrl('https://example.com/front.jpg')->hasBackPicture());
    }

    public function test_it_is_immutable(): void
    {
        $target = ImageSearchTarget::fromUrl('https://example.com/front.jpg');
        $other = $target->withBackUrl('https://example.com/back.jpg');

        $this->assertFalse($target->hasBackPicture());
        $this->assertTrue($other->hasBackPicture());
        $this->assertNotSame($target, $other);
    }

    public function test_it_rejects_a_second_back_picture(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only one back picture may be given.');

        ImageSearchTarget::fromUrl('https://example.com/front.jpg')
            ->withBackUrl('https://example.com/back.jpg')
            ->withBackBase64(base64_encode('back-bytes'));
    }

    public function test_it_rejects_a_malformed_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Picture URL must be a valid URL.');

        ImageSearchTarget::fromUrl('not a url');
    }

    public function test_it_rejects_a_non_http_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Picture URL must use the http or https scheme.');

        ImageSearchTarget::fromUrl('ftp://example.com/front.jpg');
    }

    public function test_it_accepts_a_plain_http_url(): void
    {
        $parts = ImageSearchTarget::fromUrl('http://example.com/front.jpg')->toMultipart();

        $this->assertSame('http://example.com/front.jpg', $parts[0]->value);
    }

    public function test_it_rejects_empty_base64_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Picture data must not be empty.');

        ImageSearchTarget::fromBase64('');
    }

    public function test_it_rejects_invalid_base64_data(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Picture data must be valid base64.');

        ImageSearchTarget::fromBase64('not base64!!');
    }

    public function test_it_rejects_an_empty_path(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Picture path must not be empty.');

        ImageSearchTarget::fromFile('');
    }

    public function test_it_rejects_a_missing_file(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not exist or is not readable');

        ImageSearchTarget::fromFile(sys_get_temp_dir() . '/colnect-missing-' . uniqid() . '.jpg');
    }

    private function createPictureFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'colnect');

        self::assertIsString($path);

        file_put_contents($path, 'image-bytes');

        $this->file = $path;

        return $path;
    }
}
