<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
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
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
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
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new ArticleVoRNormalizer(new ArticlesClient($this->getHttpClient()));
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
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
        $articleVoR = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new ArraySequence([]), [], promise_for(null),
            promise_for(null), promise_for(new Copyright('license', 'statement', 'holder')),
            new ArraySequence([new PersonAuthor(new Person('preferred name', 'index name'))]), null, null,
            new ArraySequence([]), promise_for(null),
            new ArraySequence([new Section('section', 'sectionId', [new Paragraph('paragraph')])]),
            new ArraySequence([]), promise_for(null), new ArraySequence([]), promise_for(null));

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
    public function it_normalize_article_vors(ArticleVoR $articleVoR, array $context, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($articleVoR, null, $context));
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
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_article_vors(
        ArticleVoR $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, ArticleVoR::class, null, $context);

        $this->mockSubjectCall(1);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
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
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject 1 impact statement'),
            promise_for($image));
        $date = new DateTimeImmutable();
        $statusDate = new DateTimeImmutable('-1 day');

        return [
            'complete' => [
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title prefix', 'title', $date, $statusDate, 1,
                    'elocationId', 'http://www.example.com/', new ArraySequence([$subject]), ['research organism'],
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('abstract')]), 'abstractDoi')),
                    promise_for(1), promise_for(new Copyright('license', 'statement', 'holder')),
                    new ArraySequence([new PersonAuthor(new Person('preferred name', 'index name'))]),
                    'impact statement', $image, new ArraySequence(['keyword']),
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('digest')]), 'digestDoi')),
                    new ArraySequence([new Section('Section', 'section', [new Paragraph('content')])]),
                    new ArraySequence([
                        new BookReference(ReferenceDate::fromString('2000-01-01'),
                            [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'book title',
                            new Place(null, null, ['publisher']), 'volume', 'edition', '10.1000/182', 18183754,
                            '978-3-16-148410-0'),
                    ]), promise_for(new ArticleSection(new ArraySequence([new Paragraph('Decision letter content')]),
                        'decisionLetterDoi')), new ArraySequence([new Paragraph('Decision letter description')]),
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('Author response content')]),
                        'authorResponseDoi'))),
                [],
                [
                    'id' => 'id',
                    'version' => 1,
                    'type' => 'type',
                    'doi' => 'doi',
                    'authorLine' => 'author line',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'statusDate' => $statusDate->format(DATE_ATOM),
                    'volume' => 1,
                    'elocationId' => 'elocationId',
                    'titlePrefix' => 'title prefix',
                    'pdf' => 'http://www.example.com/',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1 name'],
                    ],
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
                    'image' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                900 => 'https://placehold.it/900x450',
                                1800 => 'https://placehold.it/1800x900',
                            ],
                            '16:9' => [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                70 => 'https://placehold.it/70x70',
                                140 => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
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
                    'decisionLetter' => [
                        'description' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Decision letter description',
                            ],
                        ],
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Decision letter content',
                            ],
                        ],
                        'doi' => 'decisionLetterDoi',
                    ],
                    'authorResponse' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Author response content',
                            ],
                        ],
                        'doi' => 'authorResponseDoi',
                    ],
                ],
            ],
            'minimum' => [
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', $date, $statusDate, 1,
                    'elocationId', null, new ArraySequence([]), [], promise_for(null), promise_for(null),
                    promise_for(new Copyright('license', 'statement')),
                    new ArraySequence([new PersonAuthor(new Person('preferred name', 'index name'))]), null, null,
                    new ArraySequence([]), promise_for(null),
                    new ArraySequence([new Section('Section', 'section', [new Paragraph('content')])]),
                    new ArraySequence([]), promise_for(null), new ArraySequence([]), promise_for(null)),
                [],
                [
                    'id' => 'id',
                    'version' => 1,
                    'type' => 'type',
                    'doi' => 'doi',
                    'authorLine' => 'author line',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'statusDate' => $statusDate->format(DATE_ATOM),
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
            'complete snippet' => [
                new ArticleVoR('article1', 1, 'research-article', '10.7554/eLife1', 'Author et al',
                    'Article 1 title prefix', 'Article 1 title', $date, $statusDate, 1, 'e1', 'http://www.example.com/',
                    new ArraySequence([$subject]), ['Article 1 research organism'],
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 1 abstract text')]),
                        '10.7554/eLife.1abstract')), promise_for(1),
                    promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                    new ArraySequence([new PersonAuthor(new Person('Author', 'Author'))]), 'Article 1 impact statement',
                    $image, new ArraySequence(['Article 1 keyword']),
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 1 digest')]),
                        '10.7554/eLife.1digest')), new ArraySequence([
                        new Section('Article 1 section title', 'article1section', [new Paragraph('Article 1 text')]),
                    ]), new ArraySequence([
                        new BookReference(ReferenceDate::fromString('2000'),
                            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'book title',
                            new Place(null, null, ['publisher'])),
                    ]),
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 1 decision letter text')]),
                        '10.7554/eLife.1decisionLetter')),
                    new ArraySequence([new Paragraph('Article 1 decision letter description')]),
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 1 author response text')]),
                        '10.7554/eLife.1authorResponse'))),
                ['snippet' => true],
                [
                    'id' => 'article1',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife1',
                    'authorLine' => 'Author et al',
                    'title' => 'Article 1 title',
                    'published' => $date->format(DATE_ATOM),
                    'statusDate' => $statusDate->format(DATE_ATOM),
                    'volume' => 1,
                    'elocationId' => 'e1',
                    'titlePrefix' => 'Article 1 title prefix',
                    'pdf' => 'http://www.example.com/',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1 name'],
                    ],
                    'researchOrganisms' => ['Article 1 research organism'],
                    'status' => 'vor',
                    'impactStatement' => 'Article 1 impact statement',
                    'image' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                900 => 'https://placehold.it/900x450',
                                1800 => 'https://placehold.it/1800x900',
                            ],
                            '16:9' => [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                70 => 'https://placehold.it/70x70',
                                140 => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall(1, true, true);
                },
            ],
            'minimum snippet' => [
                new ArticleVoR('article1', 1, 'research-article', '10.7554/eLife1', 'Author et al', null,
                    'Article 1 title', $date, $statusDate, 1, 'e1', null, new ArraySequence([]), [], promise_for(null),
                    promise_for(null), promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                    new ArraySequence([new PersonAuthor(new Person('Author', 'Author'))]), 'Article 1 impact statement',
                    $image, new ArraySequence([]), promise_for(null), new ArraySequence([
                        new Section('Article 1 section title', 'article1section', [new Paragraph('Article 1 text')]),
                    ]), new ArraySequence([
                        new BookReference(ReferenceDate::fromString('2000'),
                            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'book title',
                            new Place(null, null, ['publisher'])),
                    ]), promise_for(null), new ArraySequence([]), promise_for(null)),
                ['snippet' => true],
                [
                    'id' => 'article1',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife1',
                    'authorLine' => 'Author et al',
                    'title' => 'Article 1 title',
                    'published' => $date->format(DATE_ATOM),
                    'statusDate' => $statusDate->format(DATE_ATOM),
                    'volume' => 1,
                    'elocationId' => 'e1',
                    'status' => 'vor',
                    'impactStatement' => 'Article 1 impact statement',
                    'image' => [
                        'alt' => '',
                        'sizes' => [
                            '2:1' => [
                                900 => 'https://placehold.it/900x450',
                                1800 => 'https://placehold.it/1800x900',
                            ],
                            '16:9' => [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                70 => 'https://placehold.it/70x70',
                                140 => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall(1, false, true);
                },
            ],
        ];
    }
}
