<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\BookChapterReferenceNormalizer;
use eLife\ApiSdk\Serializer\Reference\ReferencePagesNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class BookChapterReferenceNormalizerTest extends TestCase
{
    /** @var BookChapterReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new BookChapterReferenceNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new PersonDetailsNormalizer(),
            new PersonAuthorNormalizer(),
            new PlaceNormalizer(),
            new ReferencePagesNormalizer(),
        ]);
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
    public function it_can_normalize_book_chapter_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new BookChapterReference('id', Date::fromString('2000'), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('pages'));

        return [
            'book chapter reference' => [$reference, null, true],
            'book chapter reference with format' => [$reference, 'foo', true],
            'non-book chapter reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_book_chapter_references(BookChapterReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new BookChapterReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], true,
                    [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], true,
                    'chapter title',
                    'book title', new Place(['publisher']),
                    new ReferencePageRange('first', 'last', 'range'), 'volume', 'edition', '10.1000/182', 18183754,
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
                    'type' => 'book-chapter',
                    'chapterTitle' => 'chapter title',
                    'pages' => [
                        'first' => 'first',
                        'last' => 'last',
                        'range' => 'range',
                    ],
                ],
            ],
            'minimum' => [
                new BookChapterReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
                    [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false,
                    'chapter title', 'book title', new Place(['publisher']),
                    new StringReferencePage('pages')),
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
                                'preferred' => 'author preferred name',
                                'index' => 'author index name',
                            ],
                        ],
                    ],
                    'editors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'editor preferred name',
                                'index' => 'editor index name',
                            ],
                        ],
                    ],
                    'type' => 'book-chapter',
                    'chapterTitle' => 'chapter title',
                    'pages' => 'pages',
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
    public function it_can_denormalize_book_chapter_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'book chapter reference' => [[], BookChapterReference::class, [], true],
            'reference that is a book chapter' => [['type' => 'book-chapter'], Reference::class, [], true],
            'reference that isn\'t a book chapter' => [['type' => 'foo'], Reference::class, [], false],
            'non-book chapter reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_book_chapter_references(array $json, BookChapterReference $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, BookChapterReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'book-chapter',
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
                    'chapterTitle' => 'chapter title',
                    'bookTitle' => 'book title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'pages' => [
                        'first' => 'first',
                        'last' => 'last',
                        'range' => 'range',
                    ],
                    'volume' => 'volume',
                    'edition' => 'edition',
                    'doi' => '10.1000/182',
                    'pmid' => 18183754,
                    'isbn' => '978-3-16-148410-0',
                ],
                new BookChapterReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], true,
                    [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], true,
                    'chapter title',
                    'book title', new Place(['publisher']),
                    new ReferencePageRange('first', 'last', 'range'), 'volume', 'edition', '10.1000/182', 18183754,
                    '978-3-16-148410-0'),
            ],
            'minimum' => [
                [
                    'type' => 'book-chapter',
                    'id' => 'id',
                    'date' => '2000',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'author preferred name',
                                'index' => 'author index name',
                            ],
                        ],
                    ],
                    'editors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'editor preferred name',
                                'index' => 'editor index name',
                            ],
                        ],
                    ],
                    'chapterTitle' => 'chapter title',
                    'bookTitle' => 'book title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'pages' => 'pages',
                ],
                new BookChapterReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
                    [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false,
                    'chapter title', 'book title', new Place(['publisher']),
                    new StringReferencePage('pages')),
            ],
        ];
    }
}
