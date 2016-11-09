<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\DataReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\DataReferenceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class DataReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var DataReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new DataReferenceNormalizer();

        new Serializer([
            $this->normalizer,
            new PersonDetailsNormalizer(),
            new PersonAuthorNormalizer(),
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
    public function it_can_normalize_data_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new DataReference('id', ReferenceDate::fromString('2000'),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false, 'title',
            'source');

        return [
            'data reference' => [$reference, null, true],
            'data reference with format' => [$reference, 'foo', true],
            'non-data reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_data_references(DataReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new DataReference('id', ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], true,
                    [new PersonAuthor(new PersonDetails('compiler preferred name', 'compiler index name'))], true,
                    [new PersonAuthor(new PersonDetails('curator preferred name', 'curator index name'))], true,
                    'title',
                    'source', 'id', new Place(null, null, ['assigning authority']), '10.1000/182',
                    'http://www.example.com/'),
                [
                    'type' => 'data',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'title' => 'title',
                    'source' => 'source',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'author preferred name',
                                'index' => 'author index name',
                            ],
                        ],
                    ],
                    'authorsEtAl' => true,
                    'compilers' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'compiler preferred name',
                                'index' => 'compiler index name',
                            ],
                        ],
                    ],
                    'compilersEtAl' => true,
                    'curators' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'curator preferred name',
                                'index' => 'curator index name',
                            ],
                        ],
                    ],
                    'curatorsEtAl' => true,
                    'dataId' => 'id',
                    'assigningAuthority' => [
                        'name' => [
                            'assigning authority',
                        ],
                    ],
                    'doi' => '10.1000/182',
                    'uri' => 'http://www.example.com/',
                ],
            ],
            'minimum' => [
                new DataReference('id', ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false,
                    'title', 'source'),
                [
                    'type' => 'data',
                    'id' => 'id',
                    'date' => '2000',
                    'title' => 'title',
                    'source' => 'source',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
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
    public function it_can_denormalize_data_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'data reference' => [[], DataReference::class, [], true],
            'reference that is data' => [['type' => 'data'], Reference::class, [], true],
            'reference that isn\'t a data' => [['type' => 'foo'], Reference::class, [], false],
            'non-data reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_data_references(array $json, DataReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, DataReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'data',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'author preferred name',
                                'index' => 'author index name',
                            ],
                        ],
                    ],
                    'authorsEtAl' => true,
                    'compilers' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'compiler preferred name',
                                'index' => 'compiler index name',
                            ],
                        ],
                    ],
                    'compilersEtAl' => true,
                    'curators' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'curator preferred name',
                                'index' => 'curator index name',
                            ],
                        ],
                    ],
                    'curatorsEtAl' => true,
                    'title' => 'title',
                    'source' => 'source',
                    'dataId' => 'id',
                    'assigningAuthority' => [
                        'name' => [
                            'assigning authority',
                        ],
                    ],
                    'doi' => '10.1000/182',
                    'uri' => 'http://www.example.com/',
                ],
                new DataReference('id', ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], true,
                    [new PersonAuthor(new PersonDetails('compiler preferred name', 'compiler index name'))], true,
                    [new PersonAuthor(new PersonDetails('curator preferred name', 'curator index name'))], true,
                    'title',
                    'source', 'id', new Place(null, null, ['assigning authority']), '10.1000/182',
                    'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'data',
                    'id' => 'id',
                    'date' => '2000',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'title' => 'title',
                    'source' => 'source',
                ],
                new DataReference('id', ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, [], false,
                    'title', 'source'),
            ],
        ];
    }
}
