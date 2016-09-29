<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\ConferenceProceedingReferenceNormalizer;
use eLife\ApiSdk\Serializer\Reference\ReferencePagesNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class ConferenceProceedingReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var ConferenceProceedingReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ConferenceProceedingReferenceNormalizer();

        new Serializer([
            $this->normalizer,
            new PersonNormalizer(),
            new PersonAuthorNormalizer(),
            new PlaceNormalizer(),
            new ReferencePagesNormalizer(),
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
    public function it_can_normalize_conference_proceeding_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new ConferenceProceedingReference(ReferenceDate::fromString('2000'),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['conference']));

        return [
            'conference proceeding reference' => [$reference, null, true],
            'conference proceeding reference with format' => [$reference, 'foo', true],
            'non-conference proceeding reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_conference_proceeding_references(
        ConferenceProceedingReference $reference,
        array $expected
    ) {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new ConferenceProceedingReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'title',
                    new Place(null, null, ['conference']), new StringReferencePage('foo'), '10.1000/182',
                    'http://www.example.com/'),
                [
                    'type' => 'conference-proceeding',
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
                    'articleTitle' => 'title',
                    'conference' => [
                        'name' => [
                            'conference',
                        ],
                    ],
                    'authorsEtAl' => true,
                    'pages' => 'foo',
                    'doi' => '10.1000/182',
                    'uri' => 'http://www.example.com/',
                ],
            ],
            'minimum' => [
                new ConferenceProceedingReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))],
                    false, 'title', new Place(null, null, ['conference'])),
                [
                    'type' => 'conference-proceeding',
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
                    'articleTitle' => 'title',
                    'conference' => [
                        'name' => [
                            'conference',
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
    public function it_can_denormalize_conference_proceeding_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'conference proceeding reference' => [[], ConferenceProceedingReference::class, [], true],
            'reference that is a conference proceeding' => [
                ['type' => 'conference-proceeding'],
                Reference::class,
                [],
                true,
            ],
            'reference that isn\'t a conference proceeding' => [['type' => 'foo'], Reference::class, [], false],
            'non-conference proceeding reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_conference_proceeding_reference(array $json, ConferenceProceedingReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, ConferenceProceedingReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'conference-proceeding',
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
                    'articleTitle' => 'title',
                    'conference' => [
                        'name' => [
                            'conference',
                        ],
                    ],
                    'pages' => 'foo',
                    'doi' => '10.1000/182',
                    'uri' => 'http://www.example.com/',
                ],
                new ConferenceProceedingReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'title',
                    new Place(null, null, ['conference']), new StringReferencePage('foo'), '10.1000/182',
                    'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'conference-proceeding',
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
                    'articleTitle' => 'title',
                    'conference' => [
                        'name' => [
                            'conference',
                        ],
                    ],
                ],
                new ConferenceProceedingReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'title',
                    new Place(null, null, ['conference'])),
            ],
        ];
    }
}
