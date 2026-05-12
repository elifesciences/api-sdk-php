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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class ProfileNormalizerTest extends TestCase
{
    /** @var ProfileNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new ProfileNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new FileNormalizer(),
            new ImageNormalizer(),
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
    public function it_can_normalize_profiles($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $profile = new Profile(Builder::dummy(Image::class), new EmptySequence());

        return [
            'profile' => [$profile, null, true],
            'profile with format' => [$profile, 'foo', true],
            'non-profile' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
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

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_profiles($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'profile' => [[], Profile::class, [], true],
            'block that is a profile' => [['type' => 'profile'], Block::class, [], true],
            'block that isn\'t a profile' => [['type' => 'foo'], Block::class, [], false],
            'non-profile' => [[], self::class, [], false],
        ];
    }

    #[Test]
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
