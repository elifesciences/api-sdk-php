<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\BlogArticleNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class BlogArticleNormalizerTest extends ApiTestCase
{
    /** @var BlogArticleNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new BlogArticleNormalizer(new BlogClient($this->getHttpClient()));
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
    public function it_can_normalize_blog_articles($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        return [
            'blog article' => [$blogArticle, null, true],
            'blog article with format' => [$blogArticle, 'foo', true],
            'non-blog article' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_blog_articles(BlogArticle $blogArticle, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($blogArticle, null, $context));
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
    public function it_can_denormalize_blog_articles($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'blog article' => [[], BlogArticle::class, [], true],
            'blog article by type' => [['type' => 'blog-article'], Model::class, [], true],
            'non-blog article' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_blog_articles(
        BlogArticle $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, BlogArticle::class, null, $context);

        $this->mockSubjectCall(1);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable();
        $banner = new Image('',
            [new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900'])]);
        $thumbnail = new Image('', [
            new ImageSize('16:9', [
                250 => 'https://placehold.it/250x141',
                500 => 'https://placehold.it/500x281',
            ]),
            new ImageSize('1:1', [
                '70' => 'https://placehold.it/70x70',
                '140' => 'https://placehold.it/140x140',
            ]),
        ]);
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
            promise_for($banner), promise_for($thumbnail));

        return [
            'complete' => [
                new BlogArticle('id', 'title', $date, 'impact statement', new ArraySequence([new Paragraph('text')]),
                    new ArraySequence([$subject])),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'impactStatement' => 'impact statement',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1 name'],
                    ],
                ],
            ],
            'minimum' => [
                new BlogArticle('id', 'title', $date, null, new ArraySequence([new Paragraph('text')]),
                    new ArraySequence([])),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new BlogArticle('blogArticle1', 'Blog article 1 title', $date, 'Blog article 1 impact statement',
                    new ArraySequence([new Paragraph('Blog article blogArticle1 text')]), new ArraySequence([$subject])),
                ['snippet' => true, 'type' => true],
                [
                    'id' => 'blogArticle1',
                    'title' => 'Blog article 1 title',
                    'published' => $date->format(DATE_ATOM),
                    'impactStatement' => 'Blog article 1 impact statement',
                    'subjects' => [
                        ['id' => 'subject1', 'name' => 'Subject 1 name'],
                    ],
                    'type' => 'blog-article',
                ],
                function (ApiTestCase $test) {
                    $test->mockBlogArticleCall(1, true);
                },
            ],
            'minimum snippet' => [
                new BlogArticle('blogArticle1', 'Blog article 1 title', $date, null,
                    new ArraySequence([new Paragraph('Blog article blogArticle1 text')]), new ArraySequence([])),
                ['snippet' => true],
                [
                    'id' => 'blogArticle1',
                    'title' => 'Blog article 1 title',
                    'published' => $date->format(DATE_ATOM),
                ],
                function (ApiTestCase $test) {
                    $test->mockBlogArticleCall(1);
                },
            ],
        ];
    }
}
