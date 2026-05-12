<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PHPUnit\Framework\Attributes\Before as Before;

final class ParagraphNormalizerTest extends TestCase
{
    /** @var ParagraphNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new ParagraphNormalizer();
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_paragraphs($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $paragraph = new Paragraph('foo');

        return [
            'paragraph' => [$paragraph, null, true],
            'paragraph with format' => [$paragraph, 'foo', true],
            'non-paragraph' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    public function it_normalize_paragraphs()
    {
        $expected = [
            'type' => 'paragraph',
            'text' => 'foo',
        ];

        $this->assertSame($expected, $this->normalizer->normalize(new Paragraph('foo')));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_paragraphs($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'paragraph' => [[], Paragraph::class, [], true],
            'block that is a paragraph' => [['type' => 'paragraph'], Block::class, [], true],
            'block that isn\'t a paragraph' => [['type' => 'foo'], Block::class, [], false],
            'non-paragraph' => [[], self::class, [], false],
        ];
    }

    #[Test]
    public function it_denormalize_paragraphs()
    {
        $json = [
            'type' => 'paragraph',
            'text' => 'foo',
        ];

        $this->assertEquals(new Paragraph('foo'), $this->normalizer->denormalize($json, Paragraph::class));
    }
}
