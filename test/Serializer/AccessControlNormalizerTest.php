<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Serializer\AccessControlNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class AccessControlNormalizerTest extends TestCase
{
    /** @var AccessControlNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new AccessControlNormalizer();
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
    public function it_can_normalize_access_controls($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $accessControl = new AccessControl('sample');

        return [
            'access control' => [$accessControl, null, true],
            'not an access control' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_access_controls(AccessControl $accessControl, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($accessControl));
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
    public function it_can_denormalize_access_controls($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'access control' => [[], AccessControl::class, [], true],
            'not an access control' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_access_controls(AccessControl $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, AccessControl::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $address = Builder::for(Address::class)->sample('simple');

        return [
            'complete' => [
                new AccessControl('sample', 'restricted'),
                [
                    'value' => 'sample',
                    'access' => 'restricted',
                ],
            ],
            'minimum' => [
                $accessControl = new AccessControl('sample'),
                [
                    'value' => 'sample',
                    'access' => 'public',
                ],
            ],
        ];
    }
}
