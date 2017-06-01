<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ThesisReference;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\ThesisReferenceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ThesisReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var ThesisReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ThesisReferenceNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new PersonDetailsNormalizer(),
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
    public function it_can_normalize_thesis_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new ThesisReference('id', Date::fromString('2000'), null,
            new PersonDetails('preferred name', 'index name'),
            'title', new Place(['publisher']));

        return [
            'thesis reference' => [$reference, null, true],
            'thesis reference with format' => [$reference, 'foo', true],
            'non-thesis reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_thesis_references(ThesisReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new ThesisReference('id', Date::fromString('2000-01-01'), 'a',
                    new PersonDetails('preferred name', 'index name'),
                    'title', new Place(['publisher']), '10.1000/182', 'http://www.example.com/'),
                [
                    'type' => 'thesis',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'author' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                    ],
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'discriminator' => 'a',
                    'doi' => '10.1000/182',
                    'uri' => 'http://www.example.com/',
                ],
            ],
            'minimum' => [
                new ThesisReference('id', Date::fromString('2000'), null,
                    new PersonDetails('preferred name', 'index name'),
                    'title', new Place(['publisher'])),
                [
                    'type' => 'thesis',
                    'id' => 'id',
                    'date' => '2000',
                    'author' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                    ],
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
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
    public function it_can_denormalize_thesis_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'thesis reference' => [[], ThesisReference::class, [], true],
            'reference that is a thesis' => [['type' => 'thesis'], Reference::class, [], true],
            'reference that isn\'t a thesis' => [['type' => 'foo'], Reference::class, [], false],
            'non-thesis reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_thesis_references(array $json, ThesisReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, ThesisReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'thesis',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'discriminator' => 'a',
                    'author' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                    ],
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'doi' => '10.1000/182',
                    'uri' => 'http://www.example.com/',
                ],
                new ThesisReference('id', Date::fromString('2000-01-01'), 'a',
                    new PersonDetails('preferred name', 'index name'),
                    'title', new Place(['publisher']), '10.1000/182', 'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'thesis',
                    'id' => 'id',
                    'date' => '2000',
                    'author' => [
                        'name' => [
                            'preferred' => 'preferred name',
                            'index' => 'index name',
                        ],
                    ],
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                ],
                new ThesisReference('id', Date::fromString('2000'), null,
                    new PersonDetails('preferred name', 'index name'),
                    'title', new Place(['publisher'])),
            ],
        ];
    }
}
