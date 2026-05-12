<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class PersonDetailsNormalizerTest extends TestCase
{
    /** @var PersonDetailsNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new PersonDetailsNormalizer();
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_people($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $person = new PersonDetails('preferred name', 'index name');

        return [
            'person' => [$person, null, true],
            'person with format' => [$person, 'foo', true],
            'non-person' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_people(PersonDetails $person, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($person));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_people($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'person' => [[], PersonDetails::class, [], true],
            'non-person' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_people(PersonDetails $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, PersonDetails::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'),
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                ],
            ],
            'minimum' => [
                $person = new PersonDetails('preferred name', 'index name'),
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                ],
            ],
        ];
    }
}
