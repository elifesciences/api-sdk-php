<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\BlogArticleNormalizer;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class BlogArticleNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

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
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
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
        $date = new DateTimeImmutable('yesterday', new DateTimeZone('Z'));
        $updatedDate = new DateTimeImmutable('now', new DateTimeZone('Z'));
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
            new EmptySequence(), promise_for($banner), promise_for($thumbnail));

        return [
            'complete' => [
                new BlogArticle('id', 'title', $date, $updatedDate, 'impact statement', new ArraySequence([new Paragraph('text')]),
                    new ArraySequence([$subject])),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updatedDate->format(ApiSdk::DATE_FORMAT),
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
                new BlogArticle('id', 'title', $date, null, null, new ArraySequence([new Paragraph('text')]),
                    new EmptySequence()),
                [],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                ],
            ],
            'complete snippet' => [
                new BlogArticle('blog-article-1', 'Blog article 1 title', $date, $updatedDate, 'Blog article 1 impact statement',
                    new ArraySequence([new Paragraph('Blog article blog-article-1 text')]), new ArraySequence([$subject])),
                ['snippet' => true, 'type' => true],
                [
                    'id' => 'blog-article-1',
                    'title' => 'Blog article 1 title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                    'updated' => $updatedDate->format(ApiSdk::DATE_FORMAT),
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
                new BlogArticle('blog-article-1', 'Blog article 1 title', $date, null, null,
                    new ArraySequence([new Paragraph('Blog article blog-article-1 text')]), new EmptySequence()),
                ['snippet' => true],
                [
                    'id' => 'blog-article-1',
                    'title' => 'Blog article 1 title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                ],
                function (ApiTestCase $test) {
                    $test->mockBlogArticleCall(1);
                },
            ],
        ];
    }

    protected function class() : string
    {
        return BlogArticle::class;
    }

    protected function samples()
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/blog-article/v2/*.json';
        yield __DIR__.'/../../vendor/elife/api/dist/samples/blog-article-list/v1/*.json#items';
        yield __DIR__."/../../vendor/elife/api/dist/samples/community-list/v1/*.json#items[?type=='blog-article']";
        yield __DIR__."/../../vendor/elife/api/dist/samples/search/v1/*.json#items[?type=='blog-article']";
    }
}
