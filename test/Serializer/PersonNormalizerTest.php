<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PersonNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var PersonNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new PersonNormalizer();
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
    public function it_can_normalize_people($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $person = new Person('preferred name', 'index name');

        return [
            'person' => [$person, null, true],
            'person with format' => [$person, 'foo', true],
            'non-person' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_people(Person $person, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($person));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Person('preferred name', 'index name', '0000-0002-1825-0097'),
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                ],
            ],
            'minimum' => [
                $person = new Person('preferred name', 'index name'),
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
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
    public function it_can_denormalize_people($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'person' => [[], Person::class, [], true],
            'non-person' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_people(array $json, Person $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Person::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                ],
                new Person('preferred name', 'index name', '0000-0002-1825-0097'),
            ],
            'minimum' => [
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                ],
                $person = new Person('preferred name', 'index name'),
            ],
        ];
    }
}
