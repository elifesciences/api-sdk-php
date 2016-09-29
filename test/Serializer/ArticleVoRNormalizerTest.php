<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeInterface;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ArticleVoRNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\SectionNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\BookReferenceNormalizer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;

final class ArticleVoRNormalizerTest extends ApiTestCase
{
    /** @var ArticleVoRNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ArticleVoRNormalizer();

        $serializer = new Serializer([
            $this->normalizer,
            new BookReferenceNormalizer(),
            new ImageNormalizer(),
            new ParagraphNormalizer(),
            new PersonNormalizer(),
            new PersonAuthorNormalizer(),
            new PlaceNormalizer(),
            new SectionNormalizer(),
            new SubjectNormalizer(),
        ]);
        $this->normalizer->setSubjects(new Subjects(new SubjectsClient($this->getHttpClient()), $serializer));
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
    public function it_can_normalize_article_vors($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $articleVoR = new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(), 1,
            'elocationId', null, new ArrayCollection([]), [], promise_for(null), promise_for(null),
            promise_for(new Copyright('license', 'statement', 'holder')),
            new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]), null,
            new ArrayCollection([]), promise_for(null),
            new ArrayCollection([new Section('section', 'sectionId', [new Paragraph('paragraph')])]),
            new ArrayCollection([]));

        return [
            'article vor' => [$articleVoR, null, true],
            'article vor with format' => [$articleVoR, 'foo', true],
            'non-article vor' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_article_vors(ArticleVoR $articleVoR, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($articleVoR));
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable();
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $subject = new Subject('subject1', 'Subject 1', null, $image);

        return [
            'complete' => [
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', $date, 1, 'elocationId',
                    'http://www.example.com/', new ArrayCollection([$subject]), ['research organism'],
                    promise_for(new ArticleSection(new ArrayCollection([new Paragraph('abstract')]), 'abstractDoi')),
                    promise_for(1), promise_for(new Copyright('license', 'statement', 'holder')),
                    new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]),
                    'impact statement', new ArrayCollection(['keyword']),
                    promise_for(new ArticleSection(new ArrayCollection([new Paragraph('digest')]), 'digestDoi')),
                    new ArrayCollection([new Section('Section', 'section', [new Paragraph('content')])]),
                    new ArrayCollection([
                        new BookReference(ReferenceDate::fromString('2000-01-01'),
                            [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'book title',
                            new Place(null, null, ['publisher']), 'volume', 'edition', '10.1000/182', 18183754,
                            '978-3-16-148410-0'),
                    ])),
                [
                    'id' => 'id',
                    'version' => 1,
                    'type' => 'type',
                    'doi' => 'doi',
                    'authorLine' => 'author line',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'volume' => 1,
                    'elocationId' => 'elocationId',
                    'pdf' => 'http://www.example.com/',
                    'subjects' => ['subject1'],
                    'researchOrganisms' => ['research organism'],
                    'copyright' => [
                        'license' => 'license',
                        'statement' => 'statement',
                        'holder' => 'holder',
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
                    'issue' => 1,
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'abstract',
                            ],
                        ],
                        'doi' => 'abstractDoi',
                    ],
                    'status' => 'vor',
                    'impactStatement' => 'impact statement',
                    'keywords' => ['keyword'],
                    'digest' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'digest',
                            ],
                        ],
                        'doi' => 'digestDoi',
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'title' => 'Section',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'content',
                                ],
                            ],
                            'id' => 'section',
                        ],
                    ],
                    'references' => [
                        [
                            'type' => 'book',
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
                            'bookTitle' => 'book title',
                            'publisher' => [
                                'name' => ['publisher'],
                            ],
                            'authorsEtAl' => true,
                            'volume' => 'volume',
                            'edition' => 'edition',
                            'doi' => '10.1000/182',
                            'pmid' => 18183754,
                            'isbn' => '978-3-16-148410-0',
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(), 1,
                    'elocationId', null, null, [], promise_for(null), promise_for(null),
                    promise_for(new Copyright('license', 'statement')),
                    new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]), null,
                    new ArrayCollection([]), promise_for(null),
                    new ArrayCollection([new Section('Section', 'section', [new Paragraph('content')])]),
                    new ArrayCollection([])),
                [
                    'id' => 'id',
                    'version' => 1,
                    'type' => 'type',
                    'doi' => 'doi',
                    'authorLine' => 'author line',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'volume' => 1,
                    'elocationId' => 'elocationId',
                    'copyright' => [
                        'license' => 'license',
                        'statement' => 'statement',
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
                    'status' => 'vor',
                    'body' => [
                        [
                            'type' => 'section',
                            'title' => 'Section',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'content',
                                ],
                            ],
                            'id' => 'section',
                        ],
                    ],
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
    public function it_can_denormalize_article_vors($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'article vor' => [[], ArticleVoR::class, [], true],
            'non-article vor' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_article_vors(array $json, ArticleVoR $expected)
    {
        $actual = $this->normalizer->denormalize($json, ArticleVoR::class);

        $normaliseResult = function ($value) use (&$normaliseResult) {
            if ($value instanceof Collection) {
                return $value->toArray();
            } elseif ($value instanceof DateTimeInterface) {
                return $value->format(DATE_ATOM);
            } elseif ($value instanceof PromiseInterface) {
                return $normaliseResult($value->wait());
            }

            return $value;
        };

        $assertObject = function ($actual, $expected) use ($normaliseResult, &$assertObject) {
            foreach (get_class_methods($actual) as $method) {
                if ('__' === substr($method, 0, 2)) {
                    continue;
                }

                $actualMethod = $normaliseResult($actual->{$method}());
                $expectedMethod = $normaliseResult($expected->{$method}());

                if (is_object($actualMethod)) {
                    $this->assertInstanceOf(get_class($actualMethod), $expectedMethod);
                    $assertObject($actualMethod, $expectedMethod);
                } else {
                    $this->assertEquals($actualMethod, $expectedMethod);
                }
            }
        };

        $this->mockSubjectCall(1);

        $assertObject($actual, $expected);
    }

    public function denormalizeProvider() : array
    {
        $date = new DateTimeImmutable();
        $image = new Image('', [
            new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900']),
            new ImageSize('16:9', [
                250 => 'https://placehold.it/250x141',
                500 => 'https://placehold.it/500x281',
            ]),
            new ImageSize('1:1', [
                '70' => 'https://placehold.it/70x70',
                '140' => 'https://placehold.it/140x140',
            ]),
        ]);
        $subject = new Subject('subject1', 'Subject 1 name', 'Subject 1 impact statement', $image);

        return [
            'complete' => [
                [
                    'id' => 'id',
                    'version' => 1,
                    'type' => 'type',
                    'doi' => 'doi',
                    'authorLine' => 'author line',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'volume' => 1,
                    'elocationId' => 'elocationId',
                    'pdf' => 'http://www.example.com/',
                    'subjects' => ['subject1'],
                    'researchOrganisms' => ['research organism'],
                    'copyright' => [
                        'license' => 'license',
                        'statement' => 'statement',
                        'holder' => 'holder',
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
                    'issue' => 1,
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'abstract',
                            ],
                        ],
                        'doi' => 'abstractDoi',
                    ],
                    'status' => 'vor',
                    'impactStatement' => 'impact statement',
                    'keywords' => ['keyword'],
                    'digest' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'digest',
                            ],
                        ],
                        'doi' => 'digestDoi',
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'title' => 'Section',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'content',
                                ],
                            ],
                            'id' => 'section',
                        ],
                    ],
                    'references' => [
                        [
                            'type' => 'book',
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
                            'bookTitle' => 'book title',
                            'publisher' => [
                                'name' => ['publisher'],
                            ],
                            'authorsEtAl' => true,
                            'volume' => 'volume',
                            'edition' => 'edition',
                            'doi' => '10.1000/182',
                            'pmid' => 18183754,
                            'isbn' => '978-3-16-148410-0',
                        ],
                    ],
                ],
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', $date, 1, 'elocationId',
                    'http://www.example.com/', new ArrayCollection([$subject]), ['research organism'],
                    promise_for(new ArticleSection(new ArrayCollection([new Paragraph('abstract')]), 'abstractDoi')),
                    promise_for(1), promise_for(new Copyright('license', 'statement', 'holder')),
                    new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]),
                    'impact statement', new ArrayCollection(['keyword']),
                    promise_for(new ArticleSection(new ArrayCollection([new Paragraph('digest')]), 'digestDoi')),
                    new ArrayCollection([new Section('Section', 'section', [new Paragraph('content')])]),
                    new ArrayCollection([
                        new BookReference(ReferenceDate::fromString('2000-01-01'),
                            [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'book title',
                            new Place(null, null, ['publisher']), 'volume', 'edition', '10.1000/182', 18183754,
                            '978-3-16-148410-0'),
                    ])),
            ],
            'minimum' => [
                [
                    'id' => 'id',
                    'version' => 1,
                    'type' => 'type',
                    'doi' => 'doi',
                    'authorLine' => 'author line',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'volume' => 1,
                    'elocationId' => 'elocationId',
                    'copyright' => [
                        'license' => 'license',
                        'statement' => 'statement',
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
                    'status' => 'vor',
                    'body' => [
                        [
                            'type' => 'section',
                            'title' => 'Section',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'content',
                                ],
                            ],
                            'id' => 'section',
                        ],
                    ],
                ],
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title', new DateTimeImmutable(), 1,
                    'elocationId', null, null, [], promise_for(null), promise_for(null),
                    promise_for(new Copyright('license', 'statement')),
                    new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]), null,
                    new ArrayCollection([]), promise_for(null),
                    new ArrayCollection([new Section('Section', 'section', [new Paragraph('content')])]),
                    new ArrayCollection([])),
            ],
        ];
    }
}
