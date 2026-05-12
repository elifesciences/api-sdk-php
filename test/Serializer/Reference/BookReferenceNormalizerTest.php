<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\BookReferenceNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class BookReferenceNormalizerTest extends TestCase
{
    /** @var BookReferenceNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new BookReferenceNormalizer();

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
    public function it_can_normalize_book_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $reference = new BookReference('id', Date::fromString('2000'), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'book title',
            new Place(['publisher']));

        return [
            'book reference' => [$reference, null, true],
            'book reference with format' => [$reference, 'foo', true],
            'non-book reference' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_book_references(BookReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new BookReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], true,
                    [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], true,
                    'book title', new Place(['publisher']), 'volume', 'edition', '10.1000/182', 18183754,
                    '978-3-16-148410-0'),
                [
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'bookTitle' => 'book title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'discriminator' => 'a',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'author preferred name',
                                'index' => 'author index name',
                            ],
                        ],
                    ],
                    'authorsEtAl' => true,
                    'editors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'editor preferred name',
                                'index' => 'editor index name',
                            ],
                        ],
                    ],
                    'editorsEtAl' => true,
                    'volume' => 'volume',
                    'edition' => 'edition',
                    'doi' => '10.1000/182',
                    'pmid' => 18183754,
                    'isbn' => '978-3-16-148410-0',
                    'type' => 'book',
                ],
            ],
            'minimum' => [
                new BookReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'book title',
                    new Place(['publisher'])),
                [
                    'id' => 'id',
                    'date' => '2000',
                    'bookTitle' => 'book title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'type' => 'book',
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
    public function it_can_denormalize_book_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'book reference' => [[], BookReference::class, [], true],
            'reference that is a book' => [['type' => 'book'], Reference::class, [], true],
            'reference that isn\'t a book' => [['type' => 'foo'], Reference::class, [], false],
            'non-book reference' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_book_references(array $json, BookReference $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, BookReference::class));
    }

    public static function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'book',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'discriminator' => 'a',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'author preferred name',
                                'index' => 'author index name',
                            ],
                        ],
                    ],
                    'authorsEtAl' => true,
                    'editors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'editor preferred name',
                                'index' => 'editor index name',
                            ],
                        ],
                    ],
                    'editorsEtAl' => true,
                    'bookTitle' => 'book title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'volume' => 'volume',
                    'edition' => 'edition',
                    'doi' => '10.1000/182',
                    'pmid' => 18183754,
                    'isbn' => '978-3-16-148410-0',
                ],
                new BookReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], true,
                    [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], true,
                    'book title', new Place(['publisher']), 'volume', 'edition', '10.1000/182', 18183754,
                    '978-3-16-148410-0'),
            ],
            'minimum' => [
                [
                    'type' => 'book',
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
                    'bookTitle' => 'book title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                ],
                new BookReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'book title',
                    new Place(['publisher'])),
            ],
        ];
    }
}
