<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Box;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Video;
use eLife\ApiSdk\Model\Block\VideoSource;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\VideoNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class VideoNormalizerTest extends TestCase
{
    /** @var VideoNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new VideoNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new FileNormalizer(),
            new ImageNormalizer(),
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
    public function it_can_normalize_videos($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $sources = [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')];
        $video = new Video(null, null, new EmptySequence(), new EmptySequence(), $sources, null, 200, 100);

        return [
            'video' => [$video, null, true],
            'video with format' => [$video, 'foo', true],
            'non-box' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_videos(Video $video, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($video));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Video('id', 'title', new ArraySequence([new Paragraph('caption')]), new ArraySequence(['attribution']),
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')],
                    Builder::dummy(Image::class), 200, 100, true, true),
                [
                    'type' => 'video',
                    'sources' => [
                        [
                            'mediaType' => 'video/mpeg',
                            'uri' => 'http://www.example.com/video.mpeg',
                        ],
                    ],
                    'width' => 200,
                    'height' => 100,
                    'id' => 'id',
                    'title' => 'title',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'caption',
                        ],
                    ],
                    'attribution' => [
                        'attribution',
                    ],
                    'placeholder' => [
                        'alt' => '',
                        'uri' => 'https://iiif.elifesciences.org/example.jpg',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
                            'filename' => 'example.jpg',
                        ],
                        'size' => [
                            'width' => 1000,
                            'height' => 500,
                        ],
                    ],
                    'autoplay' => true,
                    'loop' => true,
                ],
            ],
            'minimum' => [
                new Video(null, null, new EmptySequence(), new EmptySequence(),
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')], null, 200, 100),
                [
                    'type' => 'video',
                    'sources' => [
                        [
                            'mediaType' => 'video/mpeg',
                            'uri' => 'http://www.example.com/video.mpeg',
                        ],
                    ],
                    'width' => 200,
                    'height' => 100,
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
    public function it_can_denormalize_videos($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'video' => [[], Video::class, [], true],
            'block that is a video' => [['type' => 'video'], Block::class, [], true],
            'block that isn\'t a video' => [['type' => 'foo'], Block::class, [], false],
            'non-video' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_videos(array $json, Video $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Video::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'video',
                    'id' => 'id',
                    'title' => 'title',
                    'caption' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'caption',
                        ],
                    ],
                    'attribution' => [
                        'attribution',
                    ],
                    'sources' => [
                        [
                            'mediaType' => 'video/mpeg',
                            'uri' => 'http://www.example.com/video.mpeg',
                        ],
                    ],
                    'placeholder' => [
                        'alt' => '',
                        'uri' => 'https://iiif.elifesciences.org/example.jpg',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
                            'filename' => 'example.jpg',
                        ],
                        'size' => [
                            'width' => 1000,
                            'height' => 500,
                        ],
                    ],
                    'width' => 200,
                    'height' => 100,
                    'autoplay' => true,
                    'loop' => true,
                ],
                new Video('id', 'title', new ArraySequence([new Paragraph('caption')]), new ArraySequence(['attribution']),
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')],
                    Builder::dummy(Image::class), 200, 100, true, true),
            ],
            'minimum' => [
                [
                    'type' => 'video',
                    'sources' => [
                        [
                            'mediaType' => 'video/mpeg',
                            'uri' => 'http://www.example.com/video.mpeg',
                        ],
                    ],
                    'width' => 200,
                    'height' => 100,
                ],
                new Video(null, null, new EmptySequence(), new EmptySequence(),
                    [new VideoSource('video/mpeg', 'http://www.example.com/video.mpeg')], null, 200, 100),
            ],
        ];
    }
}
