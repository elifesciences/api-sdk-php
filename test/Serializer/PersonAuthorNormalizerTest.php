<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class PersonAuthorNormalizerTest extends TestCase
{
    /** @var PersonAuthorNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new PersonAuthorNormalizer();

        new Serializer([
            $this->normalizer,
            new AddressNormalizer(),
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
    public function it_can_normalize_people($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $personAuthor = new PersonAuthor(new PersonDetails('preferred name', 'index name'));

        return [
            'person author' => [$personAuthor, null, true],
            'person author with format' => [$personAuthor, 'foo', true],
            'non-person author' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_person_authors(PersonAuthor $personAuthor, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($personAuthor));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new PersonAuthor(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'), true,
                    ['additional information'], [new Place(null, null, ['affiliation'])], 'competing interests', 'contribution',
                    ['foo@example.com'], [1], ['+12025550182;ext=555'],
                    [
                        $somewhere = Builder::for(Address::class)->sample('somewhere'),
                    ]),
                [
                    'additionalInformation' => ['additional information'],
                    'affiliations' => [
                        [
                            'name' => ['affiliation'],
                        ],
                    ],
                    'competingInterests' => 'competing interests',
                    'contribution' => 'contribution',
                    'emailAddresses' => ['foo@example.com'],
                    'equalContributionGroups' => [1],
                    'phoneNumbers' => ['+12025550182;ext=555'],
                    'postalAddresses' => [
                        [
                            'formatted' => ['somewhere'],
                            'components' => [
                                'locality' => ['somewhere'],
                            ],
                        ],
                    ],
                    'type' => 'person',
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'deceased' => true,
                ],
            ],
            'minimum' => [
                new PersonAuthor(new PersonDetails('preferred name', 'index name')),
                [
                    'type' => 'person',
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
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
    public function it_can_denormalize_people($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'person author' => [[], PersonAuthor::class, [], true],
            'author entry that is a person' => [['type' => 'person'], AuthorEntry::class, [], true],
            'author entry that isn\'t a person' => [['type' => 'foo'], AuthorEntry::class, [], false],
            'author that is a person' => [['type' => 'person'], Author::class, [], true],
            'author that isn\'t a person' => [['type' => 'foo'], Author::class, [], false],
            'non-person author' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_people(array $json, PersonAuthor $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, PersonAuthor::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'person',
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'deceased' => true,
                    'additionalInformation' => ['additional information'],
                    'affiliations' => [
                        [
                            'name' => ['affiliation'],
                        ],
                    ],
                    'competingInterests' => 'competing interests',
                    'contribution' => 'contribution',
                    'emailAddresses' => ['foo@example.com'],
                    'equalContributionGroups' => [1],
                    'phoneNumbers' => ['+12025550182;ext=555'],
                    'postalAddresses' => [
                        [
                            'formatted' => ['somewhere'],
                            'components' => [
                                'locality' => ['somewhere'],
                            ],
                        ],
                    ],
                ],
                new PersonAuthor(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'), true,
                    ['additional information'], [new Place(null, null, ['affiliation'])], 'competing interests', 'contribution',
                    ['foo@example.com'], [1], ['+12025550182;ext=555'],
                    [
                        $somewhere = Builder::for(Address::class)->sample('somewhere'),
                    ]),
            ],
            'minimum' => [
                [
                    'type' => 'person',
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                ],
                $personAuthor = new PersonAuthor(new PersonDetails('preferred name', 'index name')),
            ],
        ];
    }
}
