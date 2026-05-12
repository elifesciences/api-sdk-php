<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\PeriodicalReference;
use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\PeriodicalReferenceNormalizer;
use eLife\ApiSdk\Serializer\Reference\ReferencePagesNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class PeriodicalReferenceNormalizerTest extends TestCase
{
    /** @var PeriodicalReferenceNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new PeriodicalReferenceNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new PersonDetailsNormalizer(),
            new PersonAuthorNormalizer(),
            new PlaceNormalizer(),
            new ReferencePagesNormalizer(),
        ]);
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_periodical_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $reference = new PeriodicalReference('id', Date::fromString('2000'), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title',
            'periodical', new StringReferencePage('pages'));

        return [
            'periodical reference' => [$reference, null, true],
            'periodical reference with format' => [$reference, 'foo', true],
            'non-periodical reference' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_periodical_references(PeriodicalReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new PeriodicalReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'article title',
                    'periodical', new ReferencePageRange('first', 'last', 'range'), 'volume',
                    'http://www.example.com/'),
                [
                    'type' => 'periodical',
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
                    'articleTitle' => 'article title',
                    'periodical' => 'periodical',
                    'pages' => [
                        'first' => 'first',
                        'last' => 'last',
                        'range' => 'range',
                    ],
                    'discriminator' => 'a',
                    'authorsEtAl' => true,
                    'volume' => 'volume',
                    'uri' => 'http://www.example.com/',
                ],
            ],
            'minimum' => [
                new PeriodicalReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title',
                    'periodical', new StringReferencePage('pages')),
                [
                    'type' => 'periodical',
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
                    'articleTitle' => 'article title',
                    'periodical' => 'periodical',
                    'pages' => 'pages',
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
    public function it_can_denormalize_periodical_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'periodical reference' => [[], PeriodicalReference::class, [], true],
            'reference that is a periodical' => [['type' => 'periodical'], Reference::class, [], true],
            'reference that isn\'t a periodical' => [['type' => 'foo'], Reference::class, [], false],
            'non-periodical reference' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_periodical_references(array $json, PeriodicalReference $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, PeriodicalReference::class));
    }

    public static function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'periodical',
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
                    'articleTitle' => 'article title',
                    'periodical' => 'periodical',
                    'pages' => [
                        'first' => 'first',
                        'last' => 'last',
                        'range' => 'range',
                    ],
                    'volume' => 'volume',
                    'uri' => 'http://www.example.com/',
                ],
                new PeriodicalReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'article title',
                    'periodical', new ReferencePageRange('first', 'last', 'range'), 'volume',
                    'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'periodical',
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
                    'articleTitle' => 'article title',
                    'periodical' => 'periodical',
                    'pages' => 'pages',
                ],
                new PeriodicalReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title',
                    'periodical', new StringReferencePage('pages')),
            ],
        ];
    }
}
