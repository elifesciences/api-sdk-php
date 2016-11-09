<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\PreprintReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\PreprintReferenceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class PreprintReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var PreprintReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new PreprintReferenceNormalizer();

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
    public function it_can_normalize_preprint_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new PreprintReference('id', ReferenceDate::fromString('2000'),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title', 'source');

        return [
            'preprint reference' => [$reference, null, true],
            'preprint reference with format' => [$reference, 'foo', true],
            'non-preprint reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_preprint_references(PreprintReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new PreprintReference('id', ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'article title',
                    'source',
                    '10.1000/182', 'http://www.example.com/'),
                [
                    'type' => 'preprint',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'articleTitle' => 'article title',
                    'source' => 'source',
                    'authorsEtAl' => true,
                    'doi' => '10.1000/182',
                    'uri' => 'http://www.example.com/',
                ],
            ],
            'minimum' => [
                new PreprintReference('id', ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title',
                    'source'),
                [
                    'type' => 'preprint',
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
                    'articleTitle' => 'article title',
                    'source' => 'source',
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
    public function it_can_denormalize_preprint_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'preprint reference' => [[], PreprintReference::class, [], true],
            'reference that is a preprint' => [['type' => 'preprint'], Reference::class, [], true],
            'reference that isn\'t a preprint' => [['type' => 'foo'], Reference::class, [], false],
            'non-preprint reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_preprint_references(array $json, PreprintReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, PreprintReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'preprint',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'authorsEtAl' => true,
                    'articleTitle' => 'article title',
                    'source' => 'source',
                    'doi' => '10.1000/182',
                    'uri' => 'http://www.example.com/',
                ],
                new PreprintReference('id', ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'article title',
                    'source',
                    '10.1000/182', 'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'preprint',
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
                    'articleTitle' => 'article title',
                    'source' => 'source',
                ],
                new PreprintReference('id', ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'article title',
                    'source'),
            ],
        ];
    }
}
