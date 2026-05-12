<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class AddressNormalizerTest extends TestCase
{
    /** @var AddressNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new AddressNormalizer();
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }


    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_addresses($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $address = Builder::for(Address::class)
            ->withSequenceOfFormatted('locality')
            ->withSequenceOfLocality('locality')
            ->__invoke();

        return [
            'address' => [$address, null, true],
            'address with format' => [$address, 'foo', true],
            'non-address' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_addresses(Address $address, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($address));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_addresses($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'address' => [[], Address::class, [], true],
            'non-address' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_addresses(Address $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Address::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                $address = Builder::for(Address::class)
                    ->withSequenceOfFormatted('address')
                    ->withSequenceOfStreetAddress('street address')
                    ->withSequenceOfLocality('locality')
                    ->withSequenceOfArea('area')
                    ->withCountry('country')
                    ->withPostalCode('postal code')
                    ->__invoke(),
                [
                    'formatted' => ['address'],
                    'components' => [
                        'streetAddress' => ['street address'],
                        'locality' => ['locality'],
                        'area' => ['area'],
                        'country' => 'country',
                        'postalCode' => 'postal code',
                    ],
                ],
            ],
            'minimum' => [
                $address = Builder::for(Address::class)
                    ->withSequenceOfFormatted('address')
                    ->__invoke(),
                [
                    'formatted' => ['address'],
                    'components' => [],
                ],
            ],
        ];
    }
}
