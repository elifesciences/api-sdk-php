<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\SoftwareReference;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\SoftwareReferenceNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class SoftwareReferenceNormalizerTest extends TestCase
{
    /** @var SoftwareReferenceNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new SoftwareReferenceNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new PersonDetailsNormalizer(),
            new PersonAuthorNormalizer(),
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
    public function it_can_normalize_software_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $reference = new SoftwareReference('id', Date::fromString('2000'), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['publisher']));

        return [
            'software reference' => [$reference, null, true],
            'software reference with format' => [$reference, 'foo', true],
            'non-software reference' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_software_references(SoftwareReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new SoftwareReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title',
                    new Place(['publisher']), '1.0', 'http://www.example.com/'),
                [
                    'type' => 'software',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'authorsEtAl' => true,
                    'discriminator' => 'a',
                    'version' => '1.0',
                    'uri' => 'http://www.example.com/',
                ],
            ],
            'minimum' => [
                new SoftwareReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
                    new Place(['publisher'])),
                [
                    'type' => 'software',
                    'id' => 'id',
                    'date' => '2000',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                ],
            ],
        ];
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_software_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'software reference' => [[], SoftwareReference::class, [], true],
            'reference that is software' => [['type' => 'software'], Reference::class, [], true],
            'reference that isn\'t software' => [['type' => 'foo'], Reference::class, [], false],
            'non-software reference' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_software_references(array $json, SoftwareReference $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, SoftwareReference::class));
    }

    public static function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'software',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'discriminator' => 'a',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'authorsEtAl' => true,
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'version' => '1.0',
                    'uri' => 'http://www.example.com/',
                ],
                new SoftwareReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title',
                    new Place(['publisher']), '1.0', 'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'software',
                    'id' => 'id',
                    'date' => '2000',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                ],
                new SoftwareReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
                    new Place(['publisher'])),
            ],
        ];
    }
}
