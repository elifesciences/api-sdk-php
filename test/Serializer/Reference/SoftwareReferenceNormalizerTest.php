<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\SoftwareReference;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\SoftwareReferenceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class SoftwareReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var SoftwareReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new SoftwareReferenceNormalizer();

        new Serializer([
            $this->normalizer,
            new PersonNormalizer(),
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
    public function it_can_normalize_software_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new SoftwareReference(ReferenceDate::fromString('2000'),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        return [
            'software reference' => [$reference, null, true],
            'software reference with format' => [$reference, 'foo', true],
            'non-software reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_software_references(SoftwareReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new SoftwareReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'title',
                    new Place(null, null, ['publisher']), '1.0', 'http://www.example.com/'),
                [
                    'type' => 'software',
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
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'authorsEtAl' => true,
                    'version' => '1.0',
                    'uri' => 'http://www.example.com/',
                ],
            ],
            'minimum' => [
                new SoftwareReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'title',
                    new Place(null, null, ['publisher'])),
                [
                    'type' => 'software',
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
    public function it_can_denormalize_software_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'software reference' => [[], SoftwareReference::class, [], true],
            'reference that is software' => [['type' => 'software'], Reference::class, [], true],
            'reference that isn\'t software' => [['type' => 'foo'], Reference::class, [], false],
            'non-software reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_software_references(array $json, SoftwareReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, SoftwareReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'software',
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
                    'title' => 'title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                    'version' => '1.0',
                    'uri' => 'http://www.example.com/',
                ],
                new SoftwareReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'title',
                    new Place(null, null, ['publisher']), '1.0', 'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'software',
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
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                ],
                new SoftwareReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'title',
                    new Place(null, null, ['publisher'])),
            ],
        ];
    }
}
