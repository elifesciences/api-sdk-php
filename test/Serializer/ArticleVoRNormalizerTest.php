<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Appendix;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVoR;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Section;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ArticleVoRNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
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
            'article vor by type' => [['type' => 'research-article', 'status' => 'vor'], Model::class, [], true],
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
        return [
            'complete' => [
                Builder::for(ArticleVoR::class)
                    ->withTitlePrefix('title prefix')
                    ->withPdf('http://www.example.com/')
                    ->withSubjects(new ArraySequence([
                        Builder::for(Subject::class)
                            ->withId('subject1')
                            ->__invoke(),
                    ]))
                    ->withPromiseOfAbstract(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 abstract text')]), '10.7554/eLife.09560abstract'))
                    ->withResearchOrganisms(['research organism'])
                    ->withDecisionLetter(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 decision letter text')]), '10.7554/eLife.09560decisionLetter')))
                    ->withAuthorResponse(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 author response text')]), '10.7554/eLife.09560authorResponse')))
                    ->__invoke(),
                [],
                [
                    'id' => '09560',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'authorLine' => 'Lee R Berger et al',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'published' => '2015-09-10T00:00:00+00:00',
                    'statusDate' => '2015-09-10T00:00:00+00:00',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'titlePrefix' => 'title prefix',
                    'pdf' => 'http://www.example.com/',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1'],
                    ],
                    'researchOrganisms' => ['research organism'],
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
                    'issue' => 1,
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 abstract text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560abstract',
                    ],
                    'status' => 'vor',
                    'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'sizes' => [
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
                        'banner' => [
                            'alt' => '',
                            'sizes' => [
                                '2:1' => [
                                    900 => 'https://placehold.it/900x450',
                                    1800 => 'https://placehold.it/1800x900',
                                ],
                            ],
                        ],
                    ],
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
                            'type' => 'book',
                            'id' => 'ref1',
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
                    ],
                    'additionalFiles' => [
                        [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://placehold.it/900x450',
                            'filename' => 'image.jpeg',
                        ],
                    ],
                    'acknowledgements' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'acknowledgements',
                        ],
                    ],
                    'ethics' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'ethics',
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
                                'text' => 'Article 09560 decision letter text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560decisionLetter',
                    ],
                    'authorResponse' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 author response text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560authorResponse',
                    ],
                ],
            ],
            'minimum' => [
                Builder::for(ArticleVoR::class)
                    ->withPromiseOfCopyright(new Copyright('license', 'statement'))
                    ->withPromiseOfIssue(null)
                    ->withPromiseOfAbstract(null)
                    ->withImpactStatement(null)
                    ->withThumbnail(null)
                    ->withPromiseOfBanner(null)
                    ->withKeywords(new ArraySequence([]))
                    ->withPromiseOfDigest(null)
                    ->withAppendices(new ArraySequence([]))
                    ->withReferences(new ArraySequence([]))
                    ->withAdditionalFiles(new ArraySequence([]))
                    ->withAcknowledgements(new ArraySequence([]))
                    ->withEthics(new ArraySequence([]))
                    ->withPromiseOfDecisionLetter(null)
                    ->withDecisionLetterDescription(new ArraySequence([]))
                    ->withPromiseOfAuthorResponse(null)
                    ->__invoke(),
                [],
                [
                    'id' => '09560',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'authorLine' => 'Lee R Berger et al',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'published' => '2015-09-10T00:00:00+00:00',
                    'statusDate' => '2015-09-10T00:00:00+00:00',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'copyright' => [
                        'license' => 'license',
                        'statement' => 'statement',
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
                    ->withPdf('http://www.example.com/')
                    ->withSubjects(new ArraySequence([
                        Builder::for(Subject::class)
                            ->withId('subject1')
                            ->__invoke(),
                    ]))
                    ->withPromiseOfAbstract(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 abstract text')]), '10.7554/eLife.09560abstract'))
                    ->withResearchOrganisms(['research organism'])
                    ->withDecisionLetter(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 decision letter text')]), '10.7554/eLife.09560decisionLetter')))
                    ->withDecisionLetterDescription(new ArraySequence([new Paragraph('Article 09560 decision letter description')]))
                    ->withAuthorResponse(promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 09560 author response text')]), '10.7554/eLife.09560authorResponse')))
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => '09560',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'authorLine' => 'Lee R Berger et al',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'published' => '2015-09-10T00:00:00+00:00',
                    'statusDate' => '2015-09-10T00:00:00+00:00',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'titlePrefix' => 'title prefix',
                    'pdf' => 'http://www.example.com/',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1'],
                    ],
                    'researchOrganisms' => ['research organism'],
                    'status' => 'vor',
                    'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'sizes' => [
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
                function (ApiTestCase $test) {
                    $test->mockArticleCall('09560', true, true);
                },
            ],
            'minimum snippet' => [
                Builder::for(ArticleVoR::class)
                    ->withPromiseOfIssue(null)
                    ->withPromiseOfAbstract(null)
                    ->withImpactStatement(null)
                    ->withThumbnail(null)
                    ->withPromiseOfBanner(null)
                    ->withKeywords(new ArraySequence([]))
                    ->withPromiseOfDigest(null)
                    ->withAppendices(new ArraySequence([]))
                    ->withReferences(new ArraySequence([]))
                    ->withAdditionalFiles(new ArraySequence([]))
                    ->withAcknowledgements(new ArraySequence([]))
                    ->withEthics(new ArraySequence([]))
                    ->withPromiseOfDecisionLetter(null)
                    ->withDecisionLetterDescription(new ArraySequence([]))
                    ->withPromiseOfAuthorResponse(null)
                    ->__invoke(),
                ['snippet' => true],
                [
                    'id' => '09560',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.09560',
                    'authorLine' => 'Lee R Berger et al',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'published' => '2015-09-10T00:00:00+00:00',
                    'statusDate' => '2015-09-10T00:00:00+00:00',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'status' => 'vor',
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall('09560', false, true);
                },
            ],
        ];
    }
}
