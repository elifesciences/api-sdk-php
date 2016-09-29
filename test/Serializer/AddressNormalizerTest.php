<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AddressNormalizerTest extends PHPUnit_Framework_TestCase
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
        $address = new Address(['locality'], [], ['locality']);

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

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Address(['address'], ['street address'], ['locality'], ['area'], 'country', 'postal code'),
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
                new Address(['address']),
                [
                    'formatted' => ['address'],
                    'components' => [],
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
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_addresses(array $json, Address $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Address::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
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
                new Address(['address'], ['street address'], ['locality'], ['area'], 'country', 'postal code'),
            ],
            'minimum' => [
                [
                    'formatted' => ['address'],
                ],
                new Address(['address']),
            ],
        ];
    }
}
