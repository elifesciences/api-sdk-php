<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Serializer\AppendixNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class AppendixNormalizerTest extends ApiTestCase
{
    /** @var AppendixNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new AppendixNormalizer();
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_appendices($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $appendix = new Appendix(
            'id',
            'title',
            new ArraySequence([
                new Section(
                    'Section title',
                    'id-section',
                    new ArraySequence([new Paragraph('Text')])
                ),
            ]),
            '10.7554/eLife.09560.app1'
        );

        return [
            'appendix' => [$appendix, null, true],
            'appendix with format' => [$appendix, 'foo', true],
            'non-appendix' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_appendices(Appendix $appendix, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($appendix));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_appendices($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'appendix' => [[], Appendix::class, [], true],
            'non-appendix' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_appendices(Appendix $expected, array $json)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Appendix::class));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Appendix(
                    'id',
                    'title',
                    new ArraySequence([new Section('Section title', 'id-section', new ArraySequence([new Paragraph('Text')]))]),
                    '10.7554/eLife.09560.app1'
                ),
                [
                    'id' => 'id',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'section',
                            'title' => 'Section title',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Text',
                                ],
                            ],
                            'id' => 'id-section',
                        ],
                    ],
                    'doi' => '10.7554/eLife.09560.app1',
                ],
            ],
            'minimum' => [
                new Appendix(
                    'id',
                    'title',
                    new ArraySequence([new Section('Section title', 'id-section', new ArraySequence([new Paragraph('Text')]))])
                ),
                [
                    'id' => 'id',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'section',
                            'title' => 'Section title',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Text',
                                ],
                            ],
                            'id' => 'id-section',
                        ],
                    ],
                ],
            ],
        ];
    }
}
