<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Profile;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\ProfileNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class ProfileNormalizerTest extends TestCase
{
    /** @var ProfileNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ProfileNormalizer();

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
    public function it_can_normalize_profiles($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $profile = new Profile(Builder::dummy(Image::class), new EmptySequence());

        return [
            'profile' => [$profile, null, true],
            'profile with format' => [$profile, 'foo', true],
            'non-profile' => [$this, null, false],
        ];
    }

    /**
     * @test
     */
    public function it_normalize_profiles()
    {
        $expected = [
            'type' => 'profile',
            'image' => [
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
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'content',
                ],
            ],
        ];

        $this->assertSame($expected, $this->normalizer->normalize(new Profile(Builder::dummy(Image::class), new ArraySequence([new Paragraph('content')]))));
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
    public function it_can_denormalize_profiles($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'profile' => [[], Profile::class, [], true],
            'block that is a profile' => [['type' => 'profile'], Block::class, [], true],
            'block that isn\'t a profile' => [['type' => 'foo'], Block::class, [], false],
            'non-profile' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalize_profiles()
    {
        $json = [
            'type' => 'profile',
            'image' => [
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
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'content',
                ],
            ],
        ];

        $this->assertObjectsAreEqual(new Profile(Builder::dummy(Image::class), new ArraySequence([new Paragraph('content')])), $this->normalizer->denormalize($json, Profile::class));
    }
}
