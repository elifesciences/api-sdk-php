<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\PatentReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\Reference\PatentReferenceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class PatentReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var PatentReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new PatentReferenceNormalizer();

        new Serializer([
            $this->normalizer,
            new PersonNormalizer(),
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
    public function it_can_normalize_patent_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new PatentReference(ReferenceDate::fromString('2000'),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        return [
            'patent reference' => [$reference, null, true],
            'patent reference with format' => [$reference, 'foo', true],
            'non-patent reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_patent_references(PatentReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new PatentReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('inventor preferred name', 'inventor index name'))], true,
                    [new PersonAuthor(new Person('assignee preferred name', 'assignee index name'))], true, 'title',
                    'type', 'country', 'number', 'http://www.example.com/'),
                [
                    'type' => 'patent',
                    'date' => '2000-01-01',
                    'inventors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'inventor preferred name',
                                'index' => 'inventor index name',
                            ],
                        ],
                    ],
                    'title' => 'title',
                    'patentType' => 'type',
                    'country' => 'country',
                    'inventorsEtAl' => true,
                    'assignees' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'assignee preferred name',
                                'index' => 'assignee index name',
                            ],
                        ],
                    ],
                    'assigneesEtAl' => true,
                    'number' => 'number',
                    'uri' => 'http://www.example.com/',
                ],
            ],
            'minimum' => [
                new PatentReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
                    'country'),
                [
                    'type' => 'patent',
                    'date' => '2000',
                    'inventors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'title' => 'title',
                    'patentType' => 'type',
                    'country' => 'country',
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
    public function it_can_denormalize_patent_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'patent reference' => [[], PatentReference::class, [], true],
            'reference that is a patent' => [['type' => 'patent'], Reference::class, [], true],
            'reference that isn\'t a patent' => [['type' => 'foo'], Reference::class, [], false],
            'non-patent reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_patent_references(array $json, PatentReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, PatentReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'patent',
                    'date' => '2000-01-01',
                    'inventors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'inventor preferred name',
                                'index' => 'inventor index name',
                            ],
                        ],
                    ],
                    'inventorsEtAl' => true,
                    'assignees' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'assignee preferred name',
                                'index' => 'assignee index name',
                            ],
                        ],
                    ],
                    'assigneesEtAl' => true,
                    'title' => 'title',
                    'patentType' => 'type',
                    'country' => 'country',
                    'number' => 'number',
                    'uri' => 'http://www.example.com/',
                ],
                new PatentReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('inventor preferred name', 'inventor index name'))], true,
                    [new PersonAuthor(new Person('assignee preferred name', 'assignee index name'))], true, 'title',
                    'type', 'country', 'number', 'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'patent',
                    'date' => '2000',
                    'inventors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'title' => 'title',
                    'patentType' => 'type',
                    'country' => 'country',
                ],
                new PatentReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
                    'country'),
            ],
        ];
    }
}
