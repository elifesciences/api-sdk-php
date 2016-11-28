<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\MediumArticle;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\MediumArticleNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\TestCase;

final class MediumArticleNormalizerTest extends TestCase
{
    /** @var MediumArticleNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new MediumArticleNormalizer();

        new Serializer([$this->normalizer, new ImageNormalizer()]);
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
    public function it_can_normalize_medium_articles($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $mediumArticle = new MediumArticle('id', 'name', null, new DateTimeImmutable('now', new DateTimeZone('Z')));

        return [
            'medium article' => [$mediumArticle, null, true],
            'medium article with format' => [$mediumArticle, 'foo', true],
            'non-medium article' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_medium_articles(MediumArticle $mediumArticle, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($mediumArticle));
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
    public function it_can_denormalize_medium_articles($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'medium article' => [[], MediumArticle::class, [], true],
            'non-medium article' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_medium_articles(MediumArticle $expected, array $json)
    {
        $actual = $this->normalizer->denormalize($json, MediumArticle::class);

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public function normalizeProvider() : array
    {
        $date = (new DateTimeImmutable('now', new DateTimeZone('Z')))->setTimezone(new DateTimeZone('Z'));
        $image = new Image('alt', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);

        return [
            'complete' => [
                new MediumArticle('http://www.example.com/', 'title', 'impact statement', $date, $image),
                [
                    'uri' => 'http://www.example.com/',
                    'title' => 'title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                    'impactStatement' => 'impact statement',
                    'image' => ['alt' => 'alt', 'sizes' => ['2:1' => [900 => 'https://placehold.it/900x450']]],
                ],
            ],
            'minimum' => [
                new MediumArticle('http://www.example.com/', 'title', null, $date, null),
                [
                    'uri' => 'http://www.example.com/',
                    'title' => 'title',
                    'published' => $date->format(ApiSdk::DATE_FORMAT),
                ],
            ],
        ];
    }
}
