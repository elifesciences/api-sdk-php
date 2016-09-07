<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeInterface;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiSdk\Client\Subjects;
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
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\ApiTestCase;
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
        $this->normalizer = new BlogArticleNormalizer();

        $serializer = new Serializer([
            $this->normalizer,
            new ImageNormalizer(),
            new SubjectNormalizer(),
            new Block\ParagraphNormalizer(),
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
    public function it_normalize_blog_articles(BlogArticle $blogArticle, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($blogArticle, null, $context));
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
                [],
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
                [],
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
            'complete snippet' => [
                new BlogArticle('id', 'title', $date, 'impact statement',
                    new PromiseCollection(rejection_for('Full blog article should not be unwrapped')),
                    new ArrayCollection([$subject])),
                ['snippet' => true],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'impactStatement' => 'impact statement',
                    'subjects' => new ArrayCollection([
                        'id',
                    ]),
                ],
            ],
            'minimum snippet' => [
                new BlogArticle('id', 'title', $date, null,
                    new PromiseCollection(rejection_for('Full blog article should not be unwrapped')), null),
                ['snippet' => true],
                [
                    'id' => 'id',
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
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
        $actual = $this->normalizer->denormalize($json, BlogArticle::class);

        $normaliseResult = function ($value) {
            if ($value instanceof Collection) {
                return new ArrayCollection($value->toArray());
            } elseif ($value instanceof DateTimeInterface) {
                return DateTimeImmutable::createFromFormat(DATE_ATOM, $value->format(DATE_ATOM));
            }

            return $value;
        };

        $this->mockSubjectCall(1);

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
                    'title' => 'title',
                    'published' => $date->format(DATE_ATOM),
                    'impactStatement' => 'impact statement',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'text',
                        ],
                    ],
                    'subjects' => ['subject1'],
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
