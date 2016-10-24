<?php

namespace test\eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
#use eLife\ApiSdk\Collection\PromiseSequence;
#use eLife\ApiSdk\Model\ArticlePoA;
#use eLife\ApiSdk\Model\ArticleSection;
#use eLife\ApiSdk\Model\Block\Paragraph;
#use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
#use eLife\ApiSdk\Model\Person;
#use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\CollectionNormalizer;
#use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;
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

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalizes_collections(Collection $collection, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($collection, null, $context));
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
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject 1 impact statement'), promise_for($banner), promise_for($thumbnail)); 

        return [
            'complete' => [
                new Collection(
                    'tropical-disease',
                    'Tropical disease',
                    'Tropical disease impact statement',
                    $date,
                    promise_for($banner),
                    $thumbnail,
                    new ArraySequence([$subject])
                ),
                ['complete' => true],
                [
                    'id' => 'tropical-disease',
                    'title' => 'Tropical disease',
                    'impact_statement' => 'Tropical disease impact statement',
                    'updated' => $date->format(DATE_ATOM),
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'sizes' => [
                                '16:9' => [
                                    250 => 'https://placehold.it/250x141',
                                    500 => 'https://placehold.it/500x281',
                                ],
                                '1:1' => [
                                    70 => 'https://placehold.it/70x70',
                                    140 => 'https://placehold.it/140x140',
                                ],
                            ],
                        ],
                        'banner' => [
                            'alt' => '',
                            'sizes' => [
                                '2:1' => [
                                    900 => 'https://placehold.it/900x450',
                                    1800 => 'https://placehold.it/1800x900',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
