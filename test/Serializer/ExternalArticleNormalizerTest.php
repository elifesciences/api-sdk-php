<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ExternalArticle;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\ExternalArticleNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class ExternalArticleNormalizerTest extends TestCase
{
    /** @var ExternalArticleNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ExternalArticleNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new PlaceNormalizer(),
        ]);
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
    public function it_can_normalize_external_articles($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $externalArticle = Builder::for(ExternalArticle::class)->__invoke();

        return [
            'external article' => [$externalArticle, null, true],
            'external article with format' => [$externalArticle, 'foo', true],
            'non-external article' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_external_articles(ExternalArticle $externalArticle, array $context, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($externalArticle, null, $context));
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
    public function it_can_denormalize_external_articles($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'external article' => [[], ExternalArticle::class, [], true],
            'external article by article type' => [['type' => 'external-article'], Article::class, [], true],
            'external article by model type' => [['type' => 'external-article'], Model::class, [], true],
            'non-external article' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_external_articles(
        ExternalArticle $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, ExternalArticle::class, null, $context);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        return [
            [
                Builder::for(ExternalArticle::class)
                    ->withArticleTitle('article title')
                    ->withJournal($journal = new Place(null, null, ['journal']))
                    ->withAuthorLine('author line')
                    ->withUri('http://www.example.com/')
                    ->__invoke(),
                [],
                [
                    'articleTitle' => 'article title',
                    'journal' => [
                        'name' => ['journal'],
                    ],
                    'authorLine' => 'author line',
                    'uri' => 'http://www.example.com/',
                ],
            ],
        ];
    }
}
