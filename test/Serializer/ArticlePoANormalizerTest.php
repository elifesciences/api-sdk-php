<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ArticlePoANormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;

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
        $articlePoA = new ArticlePoA('id', 1, 'type', 'doi', 'author line', null, 'title', new DateTimeImmutable(),
            new DateTimeImmutable(), 1, 'elocationId', null, new ArraySequence([]), [], promise_for(null),
            promise_for(null), promise_for(new Copyright('license', 'statement', 'holder')),
            new ArraySequence([new PersonAuthor(new Person('preferred name', 'index name'))]));

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
        $date = new DateTimeImmutable();
        $statusDate = new DateTimeImmutable('-1 day');
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject 1 impact statement'),
            promise_for($image));

        return [
            'complete' => [
                new ArticlePoA('id', 1, 'type', 'doi', 'author line', 'title prefix', 'title', $date, $statusDate, 2,
                    'elocationId', 'http://www.example.com/', new ArraySequence([$subject]), ['research organism'],
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('abstract')]))), promise_for(1),
                    promise_for(new Copyright('license', 'statement', 'holder')),
                    new ArraySequence([new PersonAuthor(new Person('preferred name', 'index name'))])),
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
                    'volume' => 2,
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
                    ],
                    'status' => 'poa',
                ],
            ],
            'minimum' => [
                new ArticlePoA('id', 1, 'type', 'doi', 'author line', null, 'title', $date, $statusDate, 1,
                    'elocationId', null, new ArraySequence([]), [], promise_for(null), promise_for(null),
                    promise_for(new Copyright('license', 'statement')),
                    new ArraySequence([new PersonAuthor(new Person('preferred name', 'index name'))])),
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
                    'status' => 'poa',
                ],
            ],
            'complete snippet' => [
                new ArticlePoA('article1', 1, 'research-article', '10.7554/eLife1', 'Author et al',
                    'Article 1 title prefix', 'Article 1 title', $date, $statusDate, 1, 'e1', 'http://www.example.com/',
                    new ArraySequence([$subject]), ['Article 1 research organism'],
                    promise_for(new ArticleSection(new ArraySequence([new Paragraph('Article 1 abstract text')]))),
                    promise_for(1), promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                    new ArraySequence([new PersonAuthor(new Person('Author', 'Author'))])),
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
                    'status' => 'poa',
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall(1, true);
                },
            ],
            'minimum snippet' => [
                new ArticlePoA('article1', 1, 'research-article', '10.7554/eLife1', 'Author et al', null,
                    'Article 1 title', $date, $statusDate, 1, 'e1', null, new ArraySequence([]), [], promise_for(null),
                    promise_for(null), promise_for(new Copyright('CC-BY-4.0', 'Statement', 'Author et al')),
                    new ArraySequence([new PersonAuthor(new Person('Author', 'Author'))])),
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
                    'status' => 'poa',
                ],
                function (ApiTestCase $test) {
                    $test->mockArticleCall(1);
                },
            ],
        ];
    }
}
