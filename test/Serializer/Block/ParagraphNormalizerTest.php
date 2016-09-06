<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ParagraphNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var ParagraphNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ParagraphNormalizer();
    }

    /**
     * @test
     */
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canNormalizeProvider
     */
    public function it_can_normalize_paragraphs($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $paragraph = new Paragraph('foo');

        return [
            'paragraph' => [$paragraph, null, true],
            'paragraph with format' => [$paragraph, 'foo', true],
            'non-paragraph' => [$this, null, false],
        ];
    }

    /**
     * @test
     */
    public function it_normalize_paragraphs()
    {
        $expected = [
            'type' => 'paragraph',
            'text' => 'foo',
        ];

        $this->assertSame($expected, $this->normalizer->normalize(new Paragraph('foo')));
    }

    /**
     * @test
     */
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    /**
     * @test
     * @dataProvider canDenormalizeProvider
     */
    public function it_can_denormalize_paragraphs($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'paragraph' => [[], Paragraph::class, [], true],
            'block that is a paragraph' => [['type' => 'paragraph'], Block::class, [], true],
            'block that isn\'t a paragraph' => [['type' => 'foo'], Block::class, [], false],
            'non-paragraph' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalize_paragraphs()
    {
        $json = [
            'type' => 'paragraph',
            'text' => 'foo',
        ];

        $this->assertEquals(new Paragraph('foo'), $this->normalizer->denormalize($json, Paragraph::class));
    }
}
