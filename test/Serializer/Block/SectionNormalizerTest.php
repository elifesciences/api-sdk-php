<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\SectionNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SectionNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var SectionNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new SectionNormalizer();

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
    public function it_can_normalize_sections($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $section = new Section('foo', null, new EmptySequence());

        return [
            'section' => [$section, null, true],
            'section with format' => [$section, 'foo', true],
            'non-section' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_sections(Section $image, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($image));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Section('title', 'id', new ArraySequence([new Paragraph('paragraph')])),
                [
                    'type' => 'section',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                    'id' => 'id',
                ],
            ],
            'minimum' => [
                new Section('title', null, new ArraySequence([new Paragraph('paragraph')])),
                [
                    'type' => 'section',
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
    public function it_can_denormalize_sections($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'section' => [[], Section::class, [], true],
            'block that is a section' => [['type' => 'section'], Block::class, [], true],
            'block that isn\'t a section' => [['type' => 'foo'], Block::class, [], false],
            'non-section' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_sections(array $json, Section $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Section::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'section',
                    'id' => 'id',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                ],
                new Section('title', 'id', new ArraySequence([new Paragraph('paragraph')])),
            ],
            'minimum' => [
                [
                    'type' => 'section',
                    'title' => 'title',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'paragraph',
                        ],
                    ],
                ],
                new Section('title', null, new ArraySequence([new Paragraph('paragraph')])),
            ],
        ];
    }
}
