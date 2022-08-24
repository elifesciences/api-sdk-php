<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticlePreprint;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\ArticlePreprintNormalizer;
use eLife\ApiSdk\Serializer\ReviewedPreprintNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;

final class ReviewedPreprintNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var ReviewedPreprintNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new ReviewedPreprintNormalizer();
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
    public function it_can_normalize_article_preprints($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reviewedPreprint = new ReviewedPreprint(1, 'title', 'reviewed', 'published');

        return [
            'preprint' => [$reviewedPreprint, null, true],
            'preprint with format' => [$reviewedPreprint, 'foo', true],
            'non-preprint' => [$this, null, false],
        ];
    }

    /**
     * @test
     */
    public function it_normalize_preprints()
    {
        $expected = [
            "id" => "1",
            'title' => 'title',
            'status' => 'reviewed',
            'stage' => 'published',
        ];
        $res = $this->normalizer->normalize(new ReviewedPreprint("1", 'title', 'reviewed', 'published'));
        $this->assertSame($expected, $res);
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
    public function it_can_denormalize_preprints($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'preprint' => [[], ReviewedPreprint::class, [], true],
            'non-preprint' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalize_preprints()
    {
        $json = [
            'id'=> '1',
            'status' => 'reviewed',
            'title' => 'title',
            'stage' => 'published',
        ];

        $expected = new ReviewedPreprint("1", "title", 'reviewed', 'published');

        $this->assertEquals($expected, $this->normalizer->denormalize($json, ReviewedPreprint::class));
    }

    protected function class() : string
    {
        return ReviewedPreprint::class;
    }

    protected function samples()
    {
//        yield __DIR__."/../../vendor/elife/api/dist/samples/reviewed-preprint/v1/*.json";
        yield __DIR__."/../../vendor/elife/api/dist/samples/reviewed-preprint-list/v1/*.json#items";
    }
}
