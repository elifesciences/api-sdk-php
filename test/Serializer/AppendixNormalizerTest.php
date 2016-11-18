<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Serializer\AppendixNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;

final class AppendixNormalizerTest extends ApiTestCase
{
    /** @var AppendixNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new AppendixNormalizer();
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
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
    public function it_can_normalize_appendices($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $appendix = new Appendix(
            'id',
            'title',
            new ArraySequence([
                new Section(
                    'Section title',
                    'id-section',
                    [new Paragraph('Text')]
                ),
            ]),
            '10.7554/eLife.09560.app1'
        );

        return [
            'appendix' => [$appendix, null, true],
            'appendix with format' => [$appendix, 'foo', true],
            'non-appendix' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_appendices(Appendix $appendix, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($appendix));
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
    public function it_can_denormalize_appendices($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'appendix' => [[], Appendix::class, [], true],
            'non-appendix' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_appendices(Appendix $expected, array $json)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Appendix::class));
    }

    public function normalizeProvider() :array
    {
        return [
            'complete' => [
                new Appendix(
                    'id',
                    'title',
                    new ArraySequence([new Section('Section title', 'id-section', [new Paragraph('Text')])]),
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
                    new ArraySequence([new Section('Section title', 'id-section', [new Paragraph('Text')])])
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
