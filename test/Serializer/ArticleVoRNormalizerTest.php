<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
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
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

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
        $articleVoR = new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new ArrayCollection([]), [], promise_for(null),
            promise_for(null), promise_for(new Copyright('license', 'statement', 'holder')),
            new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]), null, null,
            new ArrayCollection([]), promise_for(null),
            new ArrayCollection([new Section('section', 'sectionId', [new Paragraph('paragraph')])]),
            new ArrayCollection([]), promise_for(null), new ArrayCollection([]), promise_for(null));

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
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_article_vors(ArticleVoR $expected, array $context, array $json)
    {
        $actual = $this->normalizer->denormalize($json, ArticleVoR::class, null, $context);

        $this->mockSubjectCall(1);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function denormalizeProvider() : array
    {
        $data = $this->normalizeProvider();

        unset($data['complete snippet']);
        unset($data['minimum snippet']);

        return $data;
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
        $subject = new Subject('subject1', 'Subject 1 name', 'Subject 1 impact statement', $image);
        $date = new DateTimeImmutable();
        $statusDate = new DateTimeImmutable('-1 day');

        return [
            'complete' => [
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title prefix', 'title', $date, $statusDate, 1,
                    'elocationId', 'http://www.example.com/', new ArrayCollection([$subject]), ['research organism'],
                    promise_for(new ArticleSection(new ArrayCollection([new Paragraph('abstract')]), 'abstractDoi')),
                    promise_for(1), promise_for(new Copyright('license', 'statement', 'holder')),
                    new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]),
                    'impact statement', $image, new ArrayCollection(['keyword']),
                    promise_for(new ArticleSection(new ArrayCollection([new Paragraph('digest')]), 'digestDoi')),
                    new ArrayCollection([new Section('Section', 'section', [new Paragraph('content')])]),
                    new ArrayCollection([
                        new BookReference(ReferenceDate::fromString('2000-01-01'),
                            [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'book title',
                            new Place(null, null, ['publisher']), 'volume', 'edition', '10.1000/182', 18183754,
                            '978-3-16-148410-0'),
                    ]), promise_for(new ArticleSection(new ArrayCollection([new Paragraph('Decision letter content')]),
                        'decisionLetterDoi')), new ArrayCollection([new Paragraph('Decision letter description')]),
                    promise_for(new ArticleSection(new ArrayCollection([new Paragraph('Author response content')]),
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
                    'elocationId', null, null, [], promise_for(null), promise_for(null),
                    promise_for(new Copyright('license', 'statement')),
                    new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]), null, null,
                    new ArrayCollection([]), promise_for(null),
                    new ArrayCollection([new Section('Section', 'section', [new Paragraph('content')])]),
                    new ArrayCollection([]), promise_for(null), new ArrayCollection([]), promise_for(null)),
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
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', 'title prefix', 'title', $date, $statusDate, 1,
                    'elocationId', 'http://www.example.com/', new ArrayCollection([$subject]), ['research organism'],
                    rejection_for('Abstract should not be unwrapped'), rejection_for('Issue should not be unwrapped'),
                    rejection_for('Copyright should not be unwrapped'),
                    new PromiseCollection(rejection_for('Authors should not be unwrapped')), 'impact statement', $image,
                    new PromiseCollection(rejection_for('Keywords should not be unwrapped')),
                    rejection_for('Digest should not be unwrapped'),
                    new PromiseCollection(rejection_for('Content should not be unwrapped')),
                    new PromiseCollection(rejection_for('Authors should not be unwrapped')),
                    rejection_for('Decision letter should not be unwrapped'),
                    new PromiseCollection(rejection_for('Decision letter description should not be unwrapped')),
                    rejection_for('Author response should not be unwrapped')),
                ['snippet' => true],
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
                    'subjects' => ['subject1'],
                    'researchOrganisms' => ['research organism'],
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
                ],
            ],
            'minimum snippet' => [
                new ArticleVoR('id', 1, 'type', 'doi', 'author line', null, 'title', $date, $statusDate, 1,
                    'elocationId', null, null, [], rejection_for('Abstract should not be unwrapped'),
                    rejection_for('Issue should not be unwrapped'), rejection_for('Copyright should not be unwrapped'),
                    new PromiseCollection(rejection_for('Authors should not be unwrapped')), null, null,
                    new PromiseCollection(rejection_for('Keywords should not be unwrapped')),
                    rejection_for('Digest should not be unwrapped'),
                    new PromiseCollection(rejection_for('Content should not be unwrapped')),
                    new PromiseCollection(rejection_for('Authors should not be unwrapped')),
                    rejection_for('Decision letter should not be unwrapped'),
                    new PromiseCollection(rejection_for('Decision letter description should not be unwrapped')),
                    rejection_for('Author response should not be unwrapped')),
                ['snippet' => true],
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
                    'status' => 'vor',
                ],
            ],
        ];
    }
}
