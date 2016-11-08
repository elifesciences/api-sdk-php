<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class AddressNormalizerTest extends TestCase
{
    /** @var AddressNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new AddressNormalizer();
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
    public function it_can_normalize_addresses($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $address = Builder::for(Address::class)
            ->withSequenceOfFormatted('locality')
            ->withSequenceOfLocality('locality')
            ->__invoke();

        return [
            'address' => [$address, null, true],
            'address with format' => [$address, 'foo', true],
            'non-address' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_addresses(Address $address, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($address));
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
    public function it_can_denormalize_addresses($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'address' => [[], Address::class, [], true],
            'non-address' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_addresses(Address $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, Address::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
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
