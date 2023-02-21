<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\PublicReview;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PublicReviewNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PublicReviewNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var PublicReviewNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new PublicReviewNormalizer();

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
    public function it_can_normalize_public_reviews($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $publicReview = new PublicReview('foo', new EmptySequence());

        return [
            'public review' => [$publicReview, null, true],
            'public review with format' => [$publicReview, 'foo', true],
            'non-public review' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_public_reviews(PublicReview $publicReview, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($publicReview));
    }

    public function normalizeProvider() : array
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
    public function it_can_denormalize_public_reviews($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'public review' => [[], PublicReview::class, [], true],
            'non-public review' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_public_reviews(array $json, PublicReview $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, PublicReview::class));
    }

    public function denormalizeProvider() : array
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
