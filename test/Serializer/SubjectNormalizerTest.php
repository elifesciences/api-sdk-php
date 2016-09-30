<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\TestCase;

final class SubjectNormalizerTest extends TestCase
{
    /** @var SubjectNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new SubjectNormalizer();

        new Serializer([$this->normalizer, new ImageNormalizer()]);
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
    public function it_can_normalize_subjects($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $subject = new Subject('id', 'name', null, $image);

        return [
            'subject' => [$subject, null, true],
            'subject with format' => [$subject, 'foo', true],
            'non-subject' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_subjects(Subject $subject, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($subject));
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
    public function it_can_denormalize_subjects($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'subject' => [[], Subject::class, [], true],
            'non-subject' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_subjects(Subject $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Subject::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $image = new Image('alt', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);

        return [
            'complete' => [
                new Subject('id', 'name', 'impact statement', $image),
                [
                    'id' => 'id',
                    'name' => 'name',
                    'image' => ['alt' => 'alt', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
                    'impactStatement' => 'impact statement',
                ],
            ],
            'without impact statement' => [
                new Subject('id', 'name', null, $image),
                [
                    'id' => 'id',
                    'name' => 'name',
                    'image' => ['alt' => 'alt', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
                ],
            ],
        ];
    }
}
