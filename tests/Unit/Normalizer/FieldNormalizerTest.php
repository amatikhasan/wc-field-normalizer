<?php

declare(strict_types=1);

namespace WCFN\Tests\Unit\Normalizer;

use PHPUnit\Framework\TestCase;
use WCFN\Normalizer\FieldNormalizer;

final class FieldNormalizerTest extends TestCase
{
    private FieldNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new FieldNormalizer();
    }

    public function testItTrimsWhitespace(): void
    {
        $input = '  Hello World  ';
        $expected = 'Hello World';

        $this->assertSame($expected, $this->normalizer->normalize($input));
    }

    public function testItNormalizesLineBreaks(): void
    {
        $input = "Line 1\r\nLine 2\rLine 3";
        $expected = "Line 1\nLine 2\nLine 3";

        $this->assertSame($expected, $this->normalizer->normalize($input));
    }

    public function testItReducesMultipleSpaces(): void
    {
        $input = 'This  is    a   test';
        $expected = 'This is a test';

        $this->assertSame($expected, $this->normalizer->normalize($input));
    }

    public function testItHandlesComplexNormalization(): void
    {
        $input = "  Title  with   \r\n  weird spacing  ";
        $expected = "Title with \n weird spacing";

        $this->assertSame($expected, $this->normalizer->normalize($input));
    }

    public function testItReturnsEmptyStringForEmptyInput(): void
    {
        $this->assertSame('', $this->normalizer->normalize('   '));
        $this->assertSame('', $this->normalizer->normalize(''));
    }
}
