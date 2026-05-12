<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\AccessControlNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class AccessControlNormalizerTest extends TestCase
{
    /** @var AccessControlNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new AccessControlNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new PlaceNormalizer(),
        ]);
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_access_controls($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $accessControl = new AccessControl('sample');

        return [
            'access control' => [$accessControl, null, true],
            'not an access control' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_access_controls(AccessControl $accessControl, array $expected, array $context = [])
    {
        $this->assertSame($expected, $this->normalizer->normalize($accessControl));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_access_controls($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'access control' => [[], AccessControl::class, [], true],
            'not an access control' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_access_controls(AccessControl $expected, array $json, array $context = [])
    {
        $actual = $this->normalizer->denormalize($json, AccessControl::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
    {
        return [
            'restricted place' => [
                new AccessControl(new Place(['place']), AccessControl::ACCESS_RESTRICTED),
                [
                    'value' => [
                        'name' => ['place'],
                    ],
                    'access' => 'restricted',
                ],
                ['class' => Place::class],
            ],
            'public string' => [
                $accessControl = new AccessControl('sample', AccessControl::ACCESS_PUBLIC),
                [
                    'value' => 'sample',
                    'access' => 'public',
                ],
            ],
        ];
    }
}
