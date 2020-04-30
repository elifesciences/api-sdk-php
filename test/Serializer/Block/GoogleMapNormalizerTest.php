<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\GoogleMap;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Serializer\Block\GoogleMapNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class GoogleMapNormalizerTest extends TestCase
{
    /** @var GoogleMapNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new GoogleMapNormalizer();

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
    public function it_can_normalize_google_maps($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $youTube = new GoogleMap('foo', null, new EmptySequence(), 300, 200);

        return [
            'google-map' => [$youTube, null, true],
            'google-map with format' => [$youTube, 'foo', true],
            'non-google-map' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_youtubes(GoogleMap $youTube, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($youTube));
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
    public function it_can_denormalize_google_maps($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'google-map' => [[], GoogleMap::class, [], true],
            'block that is a google-map' => [['type' => 'google-map'], Block::class, [], true],
            'block that isn\'t a google-map' => [['type' => 'foo'], Block::class, [], false],
            'non-google-map' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_google_maps(GoogleMap $expected, array $json)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, GoogleMap::class));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new GoogleMap(
                    'foo',
                    'title1',
                    new ArraySequence([new Paragraph('paragraph1')]),
                    300,
                    200
                ),
                [
                    'type' => 'google-map',
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
                new GoogleMap(
                    'foo',
                    null,
                    new EmptySequence(),
                    300,
                    200
                ),
                [
                    'type' => 'google-map',
                    'id' => 'foo',
                    'width' => 300,
                    'height' => 200,
                ],
            ],
        ];
    }
}
