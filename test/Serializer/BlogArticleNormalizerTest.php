<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeInterface;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\BlogArticleNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class BlogArticleNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var BlogArticleNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new BlogArticleNormalizer();

        new Serializer([$this->normalizer, new Block\ParagraphNormalizer()]);
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
            new PromiseCollection(rejection_for('Full blog article should not be unwrapped')),
            new PromiseCollection(rejection_for('Subjects should not be unwrapped'))
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
    public function it_normalize_blog_articles(BlogArticle $blogArticle, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($blogArticle));
    }

    public function normalizeProvider() : array
    {
        $date = new DateTimeImmutable();
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $subject = new Subject('id', 'name', null, $image);

        return [
            'complete' => [
                new BlogArticle('id', 'title', $date, 'impact statement', new ArrayCollection([new Paragraph('text')]),
                    new ArrayCollection([$subject])),
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'impactStatement' => 'impact statement',
                    'content' => new ArrayCollection([
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ]),
                    'subjects' => new ArrayCollection([
                        'id',
                    ]),
                ],
            ],
            'minimum' => [
                new BlogArticle('id', 'title', $date, null, new ArrayCollection([new Paragraph('text')]), null),
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'content' => new ArrayCollection([
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ]),
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
    public function it_can_denormalize_blog_articles($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'blog article' => [[], BlogArticle::class, [], true],
            'non-blog article' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_blog_articles(array $json, BlogArticle $expected)
    {
        $expected->getContent()->toArray();
        $expected->getSubjects()->toArray();

        $actual = $this->normalizer->denormalize($json, BlogArticle::class);

        $normaliseResult = function ($value) {
            if ($value instanceof Collection) {
                return new ArrayCollection($value->toArray());
            } elseif ($value instanceof DateTimeInterface) {
                return DateTimeImmutable::createFromFormat(DATE_ATOM, $value->format(DATE_ATOM));
            }

            return $value;
        };

        foreach (get_class_methods(BlogArticle::class) as $method) {
            if ('__' === substr($method, 0, 2)) {
                continue;
            }

            $this->assertEquals($normaliseResult($expected->{$method}()), $normaliseResult($actual->{$method}()));
        }
    }

    public function denormalizeProvider() : array
    {
        $date = new DateTimeImmutable();
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);
        $subject = new Subject('id', 'name', null, $image);

        return [
            'complete' => [
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
                    'subjects' => new PromiseCollection(promise_for([$subject])),
                ],
                new BlogArticle('id', 'title', $date, 'impact statement', new ArrayCollection([new Paragraph('text')]),
                    new ArrayCollection([$subject])),
            ],
            'minimum' => [
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
                new BlogArticle('id', 'title', $date, null, new ArrayCollection([new Paragraph('text')]), null),
            ],
        ];
    }
}
