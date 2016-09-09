<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\MathML;
use eLife\ApiSdk\Serializer\Block\MathMLNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MathMLNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var MathMLNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new MathMLNormalizer();
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
    public function it_can_normalize_math_ml($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $mathML = new MathML(null, null, '<math></math>');

        return [
            'mathML' => [$mathML, null, true],
            'mathML with format' => [$mathML, 'foo', true],
            'non-mathML' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_math_ml(MathML $mathML, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($mathML));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new MathML('id', 'label', '<math></math>'),
                [
                    'type' => 'mathml',
                    'mathml' => '<math></math>',
                    'id' => 'id',
                    'label' => 'label',
                ],
            ],
            'minimum' => [
                new MathML(null, null, '<math></math>'),
                [
                    'type' => 'mathml',
                    'mathml' => '<math></math>',
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
    public function it_can_denormalize_math_ml($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'mathML' => [[], MathML::class, [], true],
            'block that is mathML' => [['type' => 'mathml'], Block::class, [], true],
            'block that isn\'t mathML' => [['type' => 'foo'], Block::class, [], false],
            'non-mathML' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_math_ml(array $json, MathML $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, MathML::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'mathml',
                    'id' => 'id',
                    'label' => 'label',
                    'mathml' => '<math></math>',
                ],
                new MathML('id', 'label', '<math></math>'),
            ],
            'minimum' => [
                [
                    'type' => 'mathml',
                    'mathml' => '<math></math>',
                ],
                new MathML(null, null, '<math></math>'),
            ],
        ];
    }
}
