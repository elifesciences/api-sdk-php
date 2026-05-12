<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Model\ArticlePreprint;
use eLife\ApiSdk\Serializer\ArticlePreprintNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class ArticlePreprintNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var ArticlePreprintNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new ArticlePreprintNormalizer();
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_article_preprints($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('now', new DateTimeZone('Z')));

        return [
            'preprint' => [$preprint, null, true],
            'preprint with format' => [$preprint, 'foo', true],
            'non-preprint' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    public function it_normalize_preprints()
    {
        $expected = [
            'status' => 'preprint',
            'description' => 'description',
            'uri' => 'http://www.example.com/',
            'date' => '2010-01-01T00:00:00Z',
        ];

        $this->assertSame($expected, $this->normalizer->normalize(new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('2010-01-01T00:00:00Z', new DateTimeZone('Z')))));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_preprints($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'preprint' => [[], ArticlePreprint::class, [], true],
            'non-preprint' => [[], self::class, [], false],
        ];
    }

    #[Test]
    public function it_denormalize_preprints()
    {
        $json = [
            'status' => 'preprint',
            'description' => 'description',
            'uri' => 'http://www.example.com/',
            'date' => '2010-01-01T00:00:00Z',
        ];

        $expected = new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('2010-01-01T00:00:00Z', new DateTimeZone('Z')));

        $this->assertEquals($expected, $this->normalizer->denormalize($json, ArticlePreprint::class));
    }

    protected function class() : string
    {
        return ArticlePreprint::class;
    }

    protected static function samples(): \Generator
    {
        yield __DIR__."/../../vendor/elife/api/dist/samples/article-history/v2/*.json#versions[?status=='preprint']";
    }
}
