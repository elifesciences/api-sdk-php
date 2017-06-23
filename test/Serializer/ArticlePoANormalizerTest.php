<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ArticlePoANormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class ArticlePoANormalizerTest extends ApiTestCase
{
    /** @var ArticlePoANormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new ArticlePoANormalizer(new ArticlesClient($this->getHttpClient()));
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
    public function it_can_normalize_article_poas($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $articlePoA = Builder::for(ArticlePoA::class)->__invoke();

        return [
            'article poa' => [$articlePoA, null, true],
            'article poa with format' => [$articlePoA, 'foo', true],
            'non-article poa' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_article_poas(ArticlePoA $articlePoA, array $context, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($articlePoA, null, $context));
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
    public function it_can_denormalize_article_poas($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'article poa' => [[], ArticlePoA::class, [], true],
            'article poa by article type' => [['type' => 'research-article', 'status' => 'poa'], Article::class, [], true],
            'article poa by model type' => [['type' => 'research-article', 'status' => 'poa'], Model::class, [], true],
            'non-article poa' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_article_poas(
        ArticlePoA $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, ArticlePoA::class, null, $context);

        $this->mockSubjectCall('subject1');

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                Builder::for(ArticlePoA::class)
                    ->withTitlePrefix('title prefix')
                    ->withPdf('http://www.example.com/')
                    ->withSubjects(new ArraySequence([
                        Builder::for(Subject::class)
                            ->withId('subject1')
                            ->__invoke(),
                    ]))
                    ->withResearchOrganisms(['research organism'])
                    ->__invoke(),
                [],
                [
                    'id' => '14107',
                    'stage' => 'published',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.14107',
                    'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                    'volume' => 5,
                    'elocationId' => 'e14107',
                    'published' => '2016-03-28T00:00:00Z',
                    'versionDate' => '2016-03-28T00:00:00Z',
                    'statusDate' => '2016-03-28T00:00:00Z',
                    'titlePrefix' => 'title prefix',
                    'authorLine' => 'Yongjian Huang et al',
                    'pdf' => 'http://www.example.com/',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1'],
                    ],
                    'researchOrganisms' => ['research organism'],
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 14107 abstract text',
                            ],
                        ],
                    ],
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
                        ],
                        'statement' => 'Funding statement',
                    ],
                    'dataSets' => [
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
                            'title' => 'Additional file 1',
                        ],
                    ],
                    'status' => 'poa',
                ],
                function ($test) {
                    $test->mockSubjectCall('genomics-evolutionary-biology', true);
                    $test->mockArticleCall('09560', true, $vor = true, 1);
                    $test->mockArticleCall('14107', true, false, 1);
                },
            ],
            'minimum' => [
                Builder::for(ArticlePoA::class)
                    ->withStage(ArticlePoA::STAGE_PREVIEW)
                    ->withPublished(null)
                    ->withVersionDate(null)
                    ->withStatusDate(null)
                    ->withAuthorLine(null)
                    ->withSequenceOfAuthors()
                    ->withPromiseOfCopyright(new Copyright('license', 'statement'))
                    ->withPromiseOfIssue(null)
                    ->withSequenceOfReviewers()
                    ->withAbstract(null)
                    ->withSequenceOfEthics()
                    ->withPromiseOfFunding(null)
                    ->withSequenceOfGeneratedDataSets()
                    ->withSequenceOfUsedDataSets()
                    ->withSequenceOfAdditionalFiles()
                    ->__invoke(),
                [],
                [
                    'id' => '14107',
                    'stage' => 'preview',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.14107',
                    'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                    'volume' => 5,
                    'elocationId' => 'e14107',
                    'copyright' => [
                        'license' => 'license',
                        'statement' => 'statement',
                    ],
                    'status' => 'poa',
                ],
            ],
            'complete snippet' => [
                Builder::for(ArticlePoA::class)
                    ->withTitlePrefix('title prefix')
                    ->withPdf('http://www.example.com/')
                    ->withSubjects(new ArraySequence([
                        Builder::for(Subject::class)
                            ->withId('subject1')
                            ->__invoke(),
                    ]))
                    ->withResearchOrganisms(['research organism'])
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => '14107',
                    'stage' => 'published',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.14107',
                    'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                    'volume' => 5,
                    'elocationId' => 'e14107',
                    'published' => '2016-03-28T00:00:00Z',
                    'versionDate' => '2016-03-28T00:00:00Z',
                    'statusDate' => '2016-03-28T00:00:00Z',
                    'titlePrefix' => 'title prefix',
                    'authorLine' => 'Yongjian Huang et al',
                    'pdf' => 'http://www.example.com/',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1'],
                    ],
                    'researchOrganisms' => ['research organism'],
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 14107 abstract text',
                            ],
                        ],
                    ],
                    'status' => 'poa',
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall('14107', true, false, 1);
                },
            ],
            'minimum snippet' => [
                Builder::for(ArticlePoA::class)
                    ->withStage(ArticlePoA::STAGE_PREVIEW)
                    ->withPublished(null)
                    ->withVersionDate(null)
                    ->withStatusDate(null)
                    ->withAuthorLine(null)
                    ->withPromiseOfIssue(null)
                    ->withSequenceOfReviewers()
                    ->withAbstract(null)
                    ->withSequenceOfEthics()
                    ->withPromiseOfFunding(null)
                    ->withSequenceOfGeneratedDataSets()
                    ->withSequenceOfUsedDataSets()
                    ->withSequenceOfAdditionalFiles()
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => '14107',
                    'stage' => 'preview',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.14107',
                    'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                    'volume' => 5,
                    'elocationId' => 'e14107',
                    'status' => 'poa',
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall('14107', false, false, 1);
                },
            ],
        ];
    }
}
