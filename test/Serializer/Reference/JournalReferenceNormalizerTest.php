<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\JournalReferenceNormalizer;
use eLife\ApiSdk\Serializer\Reference\ReferencePagesNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class JournalReferenceNormalizerTest extends TestCase
{
    /** @var JournalReferenceNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new JournalReferenceNormalizer();

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
    public function it_can_normalize_journal_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $reference = new JournalReference('id', Date::fromString('2000'), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title',
            'journal');

        return [
            'journal reference' => [$reference, null, true],
            'journal reference with format' => [$reference, 'foo', true],
            'non-journal reference' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_journal_references(JournalReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new JournalReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'article title',
                    'journal', new ReferencePageRange('first', 'last', 'range'), 'volume',
                    '10.1000/182', 18183754),
                [
                    'type' => 'journal',
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
                    'journal' => 'journal',
                    'pages' => [
                        'first' => 'first',
                        'last' => 'last',
                        'range' => 'range',
                    ],
                    'discriminator' => 'a',
                    'authorsEtAl' => true,
                    'volume' => 'volume',
                    'doi' => '10.1000/182',
                    'pmid' => 18183754,
                ],
            ],
            'minimum' => [
                new JournalReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title',
                    'journal'),
                [
                    'type' => 'journal',
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
                    'journal' => 'journal',
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
    public function it_can_denormalize_journal_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'journal reference' => [[], JournalReference::class, [], true],
            'reference that is a journal' => [['type' => 'journal'], Reference::class, [], true],
            'reference that isn\'t a journal' => [['type' => 'foo'], Reference::class, [], false],
            'non-journal reference' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_journal_references(array $json, JournalReference $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, JournalReference::class));
    }

    public static function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'journal',
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
                    'journal' => 'journal',
                    'pages' => [
                        'first' => 'first',
                        'last' => 'last',
                        'range' => 'range',
                    ],
                    'volume' => 'volume',
                    'doi' => '10.1000/182',
                    'pmid' => 18183754,
                ],
                new JournalReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'article title',
                    'journal', new ReferencePageRange('first', 'last', 'range'), 'volume',
                    '10.1000/182', 18183754),
            ],
            'minimum' => [
                [
                    'type' => 'journal',
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
                    'journal' => 'journal',
                ],
                new JournalReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title',
                    'journal'),
            ],
        ];
    }
}
