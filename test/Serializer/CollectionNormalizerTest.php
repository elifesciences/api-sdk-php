<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiSdk\ApiSdk;
#use eLife\ApiSdk\Collection\ArraySequence;
#use eLife\ApiSdk\Collection\PromiseSequence;
#use eLife\ApiSdk\Model\ArticlePoA;
#use eLife\ApiSdk\Model\ArticleSection;
#use eLife\ApiSdk\Model\Block\Paragraph;
#use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
#use eLife\ApiSdk\Model\ImageSize;
#use eLife\ApiSdk\Model\Person;
#use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Collection;
#use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\CollectionNormalizer;
#use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
#use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class CollectionNormalizerTest extends ApiTestCase
{
    /** @var CollectionNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new CollectionNormalizer(new CollectionsClient($this->getHttpClient()));
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
    public function it_can_normalize_collections($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $collection = new Collection('tropical-disease', 'Tropical disease', null, new DateTimeImmutable(), rejection_for('No banner'), new Image('', []));

        return [
            'collection' => [$collection, null, true],
            'collection with format' => [$collection, 'foo', true],
            'non-collection' => [$this, null, false],
        ];
    }

}
