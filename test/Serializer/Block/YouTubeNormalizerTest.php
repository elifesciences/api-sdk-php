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
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class YouTubeNormalizerTest extends TestCase
{
    /** @var YouTubeNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new YouTubeNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new ParagraphNormalizer(),
        ]);
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
    public function it_can_normalize_youtubes($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $youTube = new YouTube('foo', null, new EmptySequence(), 300, 200);

        return [
            'youtube' => [$youTube, null, true],
            'youtube with format' => [$youTube, 'foo', true],
            'non-youtube' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_youtubes(YouTube $youTube, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($youTube));
    }

    public function normalizeProvider() : array
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
    public function it_can_denormalize_youtubes($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'youtube' => [[], YouTube::class, [], true],
            'block that is a youtube' => [['type' => 'youtube'], Block::class, [], true],
            'block that isn\'t a youtube' => [['type' => 'foo'], Block::class, [], false],
            'non-youtube' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_youtubes(array $json, YouTube $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, YouTube::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'youtube',
                    'id' => 'foo',
                    'title' => 'title1',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph1',
                        ],
                    ],
                    'width' => 300,
                    'height' => 200,
                ],
                new YouTube(
                    'foo',
                    'title1',
                    new ArraySequence([new Paragraph('paragraph1')]),
                    300,
                    200
                ),
            ],
            'minimum' => [
                [
                    'type' => 'youtube',
                    'id' => 'foo',
                    'width' => 300,
                    'height' => 200,
                ],
                new YouTube(
                    'foo',
                    null,
                    new EmptySequence(),
                    300,
                    200
                ),
            ],
        ];
    }
}
