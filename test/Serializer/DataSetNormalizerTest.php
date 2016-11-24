<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Serializer\DataSetNormalizer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class DataSetNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var DataSetNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new DataSetNormalizer();

        new Serializer([
            $this->normalizer,
            new PersonDetailsNormalizer(),
            new PersonAuthorNormalizer(),
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
    public function it_can_normalize_data_sets($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $dataSet = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        return [
            'data set' => [$dataSet, null, true],
            'data set with format' => [$dataSet, 'foo', true],
            'non-data set' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_data_sets(DataSet $dataSet, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($dataSet));
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
    public function it_can_denormalize_data_sets($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'data set' => [[], DataSet::class, [], true],
            'non-data set' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_data_sets(DataSet $expected, array $json)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, DataSet::class));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new DataSet('id', new Date(2000, 1, 2), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title', 'data id', 'details', '10.1000/182', 'https://doi.org/10.1000/182'),
                [
                    'id' => 'id',
                    'date' => '2000-01-02',
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
                    'authorsEtAl' => true,
                    'dataId' => 'data id',
                    'details' => 'details',
                    'doi' => '10.1000/182',
                    'uri' => 'https://doi.org/10.1000/182',
                ],
            ],
            'minimum' => [
                new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null),
                [
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
                ],
            ],
        ];
    }
}
