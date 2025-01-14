<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\ArticlesClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PublicReview;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ArticleVoRNormalizer;
use function GuzzleHttp\Promise\promise_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class ArticleVoRNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

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
        $articleVoR = Builder::for(ArticleVoR::class)->__invoke();

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
            'article vor by article type' => [['type' => 'research-article', 'status' => 'vor'], Article::class, [], true],
            'article vor by model type' => [['type' => 'research-article', 'status' => 'vor'], Model::class, [], true],
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

        $this->mockSubjectCall('subject1');

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $datasets = [
            'complete' => [
                Builder::for(ArticleVoR::class)
                    ->withTitlePrefix('title prefix')
                    ->withDoiVersion('10.7554/eLife.09560.1')
                    ->withReviewedDate(new DateTimeImmutable('2022-09-15T00:00:00Z'))
                    ->withCurationLabels(['Landmark', 'Exceptional'])
                    ->withPdf('http://www.example.com/pdf')
                    ->withFiguresPdf('http://www.example.com/figures')
                    ->withPromiseOfXml('http://www.example.com/xml')
                    ->withSubjects(new ArraySequence([
                        Builder::for(Subject::class)
                            ->withId('subject1')
                            ->__invoke(),
                    ]))
                    ->withAbstract(new ArticleSection(new ArraySequence([new Section('Article 09560 abstract section title', null, new ArraySequence([new Paragraph('Article 09560 abstract text')]))]), '10.7554/eLife.09560abstract'))
                    ->withResearchOrganisms(['research organism'])
                    ->withEditorEvaluation(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 editor evaluation text')]), '10.7554/eLife.09560editorEvaluation', 'editor-evaluation-id')))
                    ->withDecisionLetter(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 decision letter text')]), '10.7554/eLife.09560decisionLetter', 'decision-letter-id')))
                    ->withAuthorResponse(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 author response text')]), '10.7554/eLife.09560authorResponse', 'author-response-id')))
                    ->withElifeAssessment(new ElifeAssessment(
                        'eLife assessment',
                        new ArticleSection(new ArraySequence([new Paragraph('Article 09560 elife assessment text')]), '10.7554/eLife.09560elifeAssessment', 'elife-assessment-id'),
                        ['important'],
                        ['solid']
                    ))
                    ->withRecommendationsForAuthors(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 recommendations for authors text')]), '10.7554/eLife.09560recommendationsForAuthors', 'recommendations-for-authors-id')))
                    ->withPublicReviews(new ArraySequence([new PublicReview('Public review 1', new ArraySequence([new Paragraph('Public review 1 content')]))]))
                    ->__invoke(),
                [],
                [
                    'id' => '09560',
                    'stage' => 'published',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'published' => '2015-09-10T00:00:00Z',
                    'versionDate' => '2015-09-10T00:00:00Z',
                    'statusDate' => '2015-09-10T00:00:00Z',
                    'titlePrefix' => 'title prefix',
                    'authorLine' => 'Lee R Berger et al',
                    'pdf' => 'http://www.example.com/pdf',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1'],
                    ],
                    'researchOrganisms' => ['research organism'],
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'section',
                                'title' => 'Article 09560 abstract section title',
                                'content' => [
                                    [
                                        'type' => 'paragraph',
                                        'text' => 'Article 09560 abstract text',
                                    ],
                                ],
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560abstract',
                    ],
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                        'social' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/social.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                                'filename' => 'social.jpg',
                            ],
                            'size' => [
                                'width' => 600,
                                'height' => 600,
                            ],
                        ],
                    ],
                    'xml' => 'http://www.example.com/xml',
                    'copyright' => [
                        'license' => 'CC-BY-4.0',
                        'statement' => 'Statement',
                        'holder' => 'Author et al',
                    ],
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'Author',
                                'index' => 'Author',
                            ],
                        ],
                    ],
                    'reviewers' => [
                        [
                            'name' => [
                                'preferred' => 'Reviewer',
                                'index' => 'Reviewer',
                            ],
                            'role' => 'Role',
                        ],
                    ],
                    'issue' => 1,
                    'ethics' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'ethics',
                        ],
                    ],
                    'funding' => [
                        'awards' => [
                            [
                                'id' => 'award',
                                'source' => [
                                    'name' => [
                                        'Funder',
                                    ],
                                    'funderId' => '10.13039/501100001659',
                                ],
                                'recipients' => [
                                    [
                                        'type' => 'person',
                                        'name' => [
                                            'preferred' => 'Author',
                                            'index' => 'Author',
                                        ],
                                    ],
                                ],
                                'awardId' => 'awardId',
                            ],
                            [
                                'id' => 'award2',
                                'source' => [
                                    'name' => [
                                        'Funder',
                                    ],
                                    'funderId' => '10.13039/501100001695',
                                ],
                            ],
                        ],
                        'statement' => 'Funding statement',
                    ],
                    'dataSets' => [
                        'availability' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Data availability',
                            ],
                        ],
                        'generated' => [
                            [
                                'id' => 'id',
                                'date' => '2000-01-02',
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
                                'authorsEtAl' => true,
                                'dataId' => 'data id',
                                'details' => 'details',
                                'doi' => '10.1000/182',
                                'uri' => 'https://doi.org/10.1000/182',
                            ],
                        ],
                        'used' => [
                            [
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
                                'uri' => 'http://www.example.com/',
                            ],
                        ],
                    ],
                    'additionalFiles' => [
                        [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://placehold.it/900x450',
                            'filename' => 'image.jpeg',
                            'id' => 'file1',
                            'label' => 'Additional file 1',
                        ],
                    ],
                    'status' => 'vor',
                    'doiVersion' => '10.7554/eLife.09560.1',
                    'reviewedDate' => '2022-09-15T00:00:00Z',
                    'curationLabels' => ['Landmark', 'Exceptional'],
                    'figuresPdf' => 'http://www.example.com/figures',
                    'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                    'keywords' => ['Article 09560 keyword'],
                    'digest' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 digest',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560digest',
                    ],
                    'body' => [
                        [
                            'type' => 'section',
                            'title' => 'Article 09560 section title',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Article 09560 text',
                                ],
                            ],
                            'id' => 'article09560section',
                        ],
                    ],
                    'appendices' => [
                        [
                            'id' => 'app1',
                            'title' => 'Appendix 1',
                            'content' => [
                                [
                                    'type' => 'section',
                                    'title' => 'Appendix 1 title',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'text' => 'Appendix 1 text',
                                        ],
                                    ],
                                    'id' => 'app1-1',
                                ],
                            ],
                            'doi' => '10.7554/eLife.09560.app1',
                        ],
                    ],
                    'references' => [
                        [
                            'id' => 'ref1',
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
                    'acknowledgements' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'acknowledgements',
                        ],
                    ],
                    'editorEvaluation' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 editor evaluation text',
                            ],
                        ],
                        'scietyUri' => 'https://editor-evaluation.com',
                        'doi' => '10.7554/eLife.09560editorEvaluation',
                        'id' => 'editor-evaluation-id',
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
                                'text' => 'Article 09560 decision letter text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560decisionLetter',
                        'id' => 'decision-letter-id',
                    ],
                    'elifeAssessment' =>  [
                        'title' => 'eLife assessment',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 elife assessment text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560elifeAssessment',
                        'id' => 'elife-assessment-id',
                        'significance' => ['important'],
                        'strength' => ['solid'],
                    ],
                    'recommendationsForAuthors' =>  [
                        'title' => 'Recommendations for authors',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 recommendations for authors text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560recommendationsForAuthors',
                        'id' => 'recommendations-for-authors-id',
                    ],
                    'publicReviews' => [
                        [
                            'title' => 'Public review 1',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Public review 1 content',
                                ],
                            ],
                        ],
                    ],
                    'authorResponse' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 author response text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560authorResponse',
                        'id' => 'author-response-id',
                    ],
                ],
                function ($test) {
                    $test->mockSubjectCall('genomics-evolutionary-biology', true);
                    $test->mockArticleCall('09560', true, $vor = true, 1);
                    $test->mockArticleCall('14107', true, false, 1);
                },
            ],
            'minimum' => [
                Builder::for(ArticleVoR::class)
                    ->withStage(ArticleVoR::STAGE_PREVIEW)
                    ->withPublished(null)
                    ->withVersionDate(null)
                    ->withStatusDate(null)
                    ->withReviewedDate(null)
                    ->withAuthorLine(null)
                    ->withSequenceOfAuthors()
                    ->withSequenceOfReviewers()
                    ->withPromiseOfCopyright(new Copyright('license', 'statement'))
                    ->withPromiseOfIssue(null)
                    ->withPromiseOfXml(null)
                    ->withCurationLabels([])
                    ->withAbstract(null)
                    ->withImpactStatement(null)
                    ->withThumbnail(null)
                    ->withSocialImage(null)
                    ->withKeywords(new EmptySequence())
                    ->withPromiseOfDigest(null)
                    ->withAppendices(new EmptySequence())
                    ->withReferences(new EmptySequence())
                    ->withAdditionalFiles(new EmptySequence())
                    ->withDataAvailability(new EmptySequence())
                    ->withGeneratedDataSets(new EmptySequence())
                    ->withUsedDataSets(new EmptySequence())
                    ->withAcknowledgements(new EmptySequence())
                    ->withEthics(new EmptySequence())
                    ->withPromiseOfFunding(null)
                    ->withPromiseOfEditorEvaluation(null)
                    ->withPromiseOfEditorEvaluationScietyUri(null)
                    ->withPromiseOfDecisionLetter(null)
                    ->withDecisionLetterDescription(new EmptySequence())
                    ->withPromiseOfAuthorResponse(null)
                    ->withElifeAssessment(null)
                    ->withPromiseOfRecommendationsForAuthors(null)
                    ->withPromiseOfRecommendationsForAuthorsTitle(null)
                    ->withDoiVersion(null)
                    ->withPublicReviews(new EmptySequence())
                    ->__invoke(),
                [],
                [
                    'id' => '09560',
                    'stage' => 'preview',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'copyright' => [
                        'license' => 'license',
                        'statement' => 'statement',
                    ],
                    'status' => 'vor',
                    'body' => [
                        [
                            'type' => 'section',
                            'title' => 'Article 09560 section title',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Article 09560 text',
                                ],
                            ],
                            'id' => 'article09560section',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                Builder::for(ArticleVoR::class)
                    ->withTitlePrefix('title prefix')
                    ->withDoiVersion('10.7554/eLife.09560.1')
                    ->withReviewedDate(new DateTimeImmutable('2022-09-15T00:00:00Z'))
                    ->withCurationLabels(['Landmark', 'Exceptional'])
                    ->withPdf('http://www.example.com/pdf')
                    ->withFiguresPdf('http://www.example.com/figures')
                    ->withPromiseOfXml('http://www.example.com/xml')
                    ->withSubjects(new ArraySequence([
                        Builder::for(Subject::class)
                            ->withId('subject1')
                            ->__invoke(),
                    ]))
                    ->withAbstract(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 abstract text')]), '10.7554/eLife.09560abstract'))
                    ->withResearchOrganisms(['research organism'])
                    ->withEditorEvaluation(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 editor evaluation text')]), '10.7554/eLife.09560editorEvaluation', 'editor-evaluation-id')))
                    ->withEditorEvaluationScietyUri(promise_for('https://editor-evaluation-09560.com'))
                    ->withDecisionLetter(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 decision letter text')]), '10.7554/eLife.09560decisionLetter', 'decision-letter-id')))
                    ->withDecisionLetterDescription(new ArraySequence([new Paragraph('Article 09560 decision letter description')]))
                    ->withAuthorResponse(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 author response text')]), '10.7554/eLife.09560authorResponse', 'author-response-id')))
                    ->withElifeAssessment(new ElifeAssessment(
                        'eLife assessment',
                        new ArticleSection(new ArraySequence([new Paragraph('Article 09560 elife assessment text')]), '10.7554/eLife.09560elifeAssessment', 'elife-assessment-id'),
                        ['landmark'],
                        ['solid']
                    ))
                    ->withRecommendationsForAuthors(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 recommendations for authors text')]), '10.7554/eLife.09560recommendationsForAuthors', 'recommendations-for-authors-id')))
                    ->withRecommendationsForAuthorsTitle(promise_for('Recommendations for authors'))
                    ->withPublicReviews(new ArraySequence([new PublicReview('Public review 1', new ArraySequence([new Paragraph('Public review 1 content')]))]))
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => '09560',
                    'stage' => 'published',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'published' => '2015-09-10T00:00:00Z',
                    'versionDate' => '2015-09-10T00:00:00Z',
                    'statusDate' => '2015-09-10T00:00:00Z',
                    'titlePrefix' => 'title prefix',
                    'authorLine' => 'Lee R Berger et al',
                    'pdf' => 'http://www.example.com/pdf',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1'],
                    ],
                    'researchOrganisms' => ['research organism'],
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 abstract text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560abstract',
                    ],
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                        'social' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/social.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                                'filename' => 'social.jpg',
                            ],
                            'size' => [
                                'width' => 600,
                                'height' => 600,
                            ],
                        ],
                    ],
                    'status' => 'vor',
                    'doiVersion' => '10.7554/eLife.09560.1',
                    'reviewedDate' => '2022-09-15T00:00:00Z',
                    'curationLabels' => ['Landmark', 'Exceptional'],
                    'figuresPdf' => 'http://www.example.com/figures',
                    'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                    'elifeAssessment' =>  [
                        'title' => 'eLife assessment',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 elife assessment text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560elifeAssessment',
                        'id' => 'elife-assessment-id',
                        'significance' => ['landmark'],
                        'strength' => ['solid'],
                    ]
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall('09560', true, true, 1);
                },
            ],
            'minimum snippet' => [
                Builder::for(ArticleVoR::class)
                    ->withStage(ArticleVoR::STAGE_PREVIEW)
                    ->withPublished(null)
                    ->withVersionDate(null)
                    ->withStatusDate(null)
                    ->withReviewedDate(null)
                    ->withAuthorLine(null)
                    ->withSequenceOfReviewers()
                    ->withPromiseOfIssue(null)
                    ->withPromiseOfXml(null)
                    ->withCurationLabels([])
                    ->withAbstract(null)
                    ->withImpactStatement(null)
                    ->withThumbnail(null)
                    ->withSocialImage(null)
                    ->withKeywords(new EmptySequence())
                    ->withPromiseOfDigest(null)
                    ->withAppendices(new EmptySequence())
                    ->withReferences(new EmptySequence())
                    ->withAdditionalFiles(new EmptySequence())
                    ->withDataAvailability(new EmptySequence())
                    ->withGeneratedDataSets(new EmptySequence())
                    ->withUsedDataSets(new EmptySequence())
                    ->withAcknowledgements(new EmptySequence())
                    ->withEthics(new EmptySequence())
                    ->withPromiseOfFunding(null)
                    ->withPromiseOfEditorEvaluation(null)
                    ->withPromiseOfEditorEvaluationScietyUri(null)
                    ->withPromiseOfDecisionLetter(null)
                    ->withDecisionLetterDescription(new EmptySequence())
                    ->withPromiseOfAuthorResponse(null)
                    ->withPromiseOfRecommendationsForAuthors(null)
                    ->withPromiseOfRecommendationsForAuthorsTitle(null)
                    ->withDoiVersion(null)
                    ->withPublicReviews(new EmptySequence())
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => '09560',
                    'stage' => 'preview',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'status' => 'vor',
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall('09560', false, true, 1);
                },
            ],
        ];
        return $datasets;
    }

    protected function class() : string
    {
        return ArticleVoR::class;
    }

    protected function samples()
    {
        yield __DIR__."/../../vendor/elife/api/dist/samples/article-list/v1/*.json#items[?status=='vor']";
        yield __DIR__."/../../vendor/elife/api/dist/samples/article-related/v2/*.json#[?status=='vor']";
        yield __DIR__.'/../../vendor/elife/api/dist/samples/article-vor/v8/complete.json';
        yield __DIR__."/../../vendor/elife/api/dist/samples/community-list/v1/*.json#items[?status=='vor']";
        yield __DIR__."/../../vendor/elife/api/dist/samples/search/v2/*.json#items[?status=='vor']";
    }
}
