<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\YouTube;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\YouTubeNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class YouTubeNormalizerTest extends TestCase
{
    /** @var YouTubeNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new YouTubeNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new ParagraphNormalizer(),
        ]);
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_youtubes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $youTube = new YouTube('foo', null, new EmptySequence(), 300, 200);

        return [
            'youtube' => [$youTube, null, true],
            'youtube with format' => [$youTube, 'foo', true],
            'non-youtube' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_youtubes(YouTube $youTube, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($youTube));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_youtubes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'youtube' => [[], YouTube::class, [], true],
            'block that is a youtube' => [['type' => 'youtube'], Block::class, [], true],
            'block that isn\'t a youtube' => [['type' => 'foo'], Block::class, [], false],
            'non-youtube' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_youtubes(YouTube $expected, array $json)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, YouTube::class));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new YouTube(
                    'foo',
                    'title1',
                    new ArraySequence([new Paragraph('paragraph1')]),
                    300,
                    200
                ),
                [
                    'type' => 'youtube',
                    'id' => 'foo',
                    'width' => 300,
                    'height' => 200,
                    'title' => 'title1',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph1',
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new YouTube(
                    'foo',
                    null,
                    new EmptySequence(),
                    300,
                    200
                ),
                [
                    'type' => 'youtube',
                    'id' => 'foo',
                    'width' => 300,
                    'height' => 200,
                ],
            ],
        ];
    }
}
