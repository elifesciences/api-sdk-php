<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Serializer\DataSetNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

use PHPUnit\Framework\Attributes\Before as Before;

final class DataSetNormalizerTest extends TestCase
{
    /** @var DataSetNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new DataSetNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new PersonDetailsNormalizer(),
            new PersonAuthorNormalizer(),
        ]);
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_data_sets($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $dataSet = new DataSet('id', new Date(2000), [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title', null, null, null, 'http://www.example.com/');

        return [
            'data set' => [$dataSet, null, true],
            'data set with format' => [$dataSet, 'foo', true],
            'non-data set' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_data_sets(DataSet $dataSet, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($dataSet));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_data_sets($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'data set' => [[], DataSet::class, [], true],
            'non-data set' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_data_sets(DataSet $expected, array $json)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, DataSet::class));
    }

    public static function normalizeProvider() : array
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
