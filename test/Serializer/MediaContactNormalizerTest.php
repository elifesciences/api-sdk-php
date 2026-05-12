<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\MediaContact;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\MediaContactNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class MediaContactNormalizerTest extends ApiTestCase
{
    /** @var MediaContactNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new MediaContactNormalizer();
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_media_contacts($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $mediaContact = new MediaContact(new PersonDetails('Person', 'Person'));

        return [
            'media contact' => [$mediaContact, null, true],
            'media contact with format' => [$mediaContact, 'foo', true],
            'non-media contact' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_media_contacts(MediaContact $mediaContact, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($mediaContact, null, $context));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_media_contacts($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'media contact' => [[], MediaContact::class, [], true],
            'non-media contact' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_media_contacts(
        MediaContact $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, MediaContact::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new MediaContact(new PersonDetails('preferred', 'index', '0000-0002-1825-0097'), [new Place(['Somewhere'])], ['foo@example'], ['+447700900415']),
                [],
                [
                    'name' => [
                        'preferred' => 'preferred',
                        'index' => 'index',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'affiliations' => [
                        [
                            'name' => ['Somewhere'],
                        ],
                    ],
                    'emailAddresses' => ['foo@example'],
                    'phoneNumbers' => ['+447700900415'],
                ],
            ],
            'minimum' => [
                new MediaContact(new PersonDetails('preferred', 'index')),
                [],
                [
                    'name' => [
                        'preferred' => 'preferred',
                        'index' => 'index',
                    ],
                ],
            ],
        ];
    }
}
