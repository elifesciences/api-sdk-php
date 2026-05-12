<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\PublicReview;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PublicReviewNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PHPUnit\Framework\Attributes\Before as Before;

final class PublicReviewNormalizerTest extends TestCase
{
    /** @var PublicReviewNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new PublicReviewNormalizer();

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
    public function it_can_normalize_public_reviews($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $publicReview = new PublicReview('foo', new EmptySequence());

        return [
            'public review' => [$publicReview, null, true],
            'public review with format' => [$publicReview, 'foo', true],
            'non-public review' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_public_reviews(PublicReview $publicReview, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($publicReview));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new PublicReview('title', new ArraySequence([new Paragraph('paragraph')]), 'doi', 'id'),
                [
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'doi' => 'doi',
                    'id' => 'id',
                ],
            ],
            'minimum' => [
                new PublicReview('title', new ArraySequence([new Paragraph('paragraph')])),
                [
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                ],
            ],
        ];
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_public_reviews($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'public review' => [[], PublicReview::class, [], true],
            'non-public review' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_public_reviews(array $json, PublicReview $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, PublicReview::class));
    }

    public static function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'doi' => 'doi',
                    'id' => 'id',
                ],
                new PublicReview('title', new ArraySequence([new Paragraph('paragraph')]), 'doi', 'id'),
            ],
            'minimum' => [
                [
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                ],
                new PublicReview('title', new ArraySequence([new Paragraph('paragraph')])),
            ],
        ];
    }
}
