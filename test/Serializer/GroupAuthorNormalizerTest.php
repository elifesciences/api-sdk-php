<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use eLife\ApiSdk\Serializer\GroupAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class GroupAuthorNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var GroupAuthorNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new GroupAuthorNormalizer();

        new Serializer([
            $this->normalizer,
            new AddressNormalizer(),
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
    public function it_can_normalize_group_authors($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $groupAuthor = new GroupAuthor('group', new ArraySequence([]));

        return [
            'group author' => [$groupAuthor, null, true],
            'group author with format' => [$groupAuthor, 'foo', true],
            'non-group author' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_group_authors(GroupAuthor $groupAuthor, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($groupAuthor));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new GroupAuthor('group', new ArraySequence([
                    new PersonAuthor(new Person('preferred name', 'index name', '0000-0002-1825-0097'), true,
                        [new Place(null, null, ['affiliation'])], 'competing interests', 'contribution',
                        ['foo@example.com'], [1], ['+12025550182;ext=555'],
                        [new Address(['somewhere'], [], ['somewhere'])]),
                ]), ['sub-group' => [new Person('preferred name', 'index name', '0000-0002-1825-0097')]],
                    [new Place(null, null, ['affiliation'])], 'competing interests', 'contribution',
                    ['foo@example.com'], [1], ['+12025550182;ext=555'],
                    [new Address(['somewhere'], [], ['somewhere'])]),
                [
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
                    'type' => 'group',
                    'name' => 'group',
                    'people' => [
                        [
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
                    'groups' => [
                        'sub-group' => [
                            [
                                'name' => [
                                    'preferred' => 'preferred name',
                                    'index' => 'index name',
                                ],
                                'orcid' => '0000-0002-1825-0097',
                            ],
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new GroupAuthor('group', new ArraySequence([])),
                [
                    'type' => 'group',
                    'name' => 'group',
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
    public function it_can_denormalize_group_authors($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'group author' => [[], GroupAuthor::class, [], true],
            'author entry that is a group' => [['type' => 'group'], AuthorEntry::class, [], true],
            'author entry that isn\'t a group' => [['type' => 'foo'], AuthorEntry::class, [], false],
            'non-group author' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_group_authors(array $json, GroupAuthor $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, GroupAuthor::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'group',
                    'name' => 'group',
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
                    'people' => [
                        [
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
                    'groups' => [
                        'sub-group' => [
                            [
                                'name' => [
                                    'preferred' => 'preferred name',
                                    'index' => 'index name',
                                ],
                                'orcid' => '0000-0002-1825-0097',
                            ],
                        ],
                    ],
                ],
                new GroupAuthor('group', new ArraySequence([
                    new PersonAuthor(new Person('preferred name', 'index name', '0000-0002-1825-0097'), true,
                        [new Place(null, null, ['affiliation'])], 'competing interests', 'contribution',
                        ['foo@example.com'], [1], ['+12025550182;ext=555'],
                        [new Address(['somewhere'], [], ['somewhere'])]),
                ]), ['sub-group' => [new Person('preferred name', 'index name', '0000-0002-1825-0097')]],
                    [new Place(null, null, ['affiliation'])], 'competing interests', 'contribution',
                    ['foo@example.com'], [1], ['+12025550182;ext=555'],
                    [new Address(['somewhere'], [], ['somewhere'])]),
            ],
            'minimum' => [
                [
                    'type' => 'group',
                    'name' => 'group',
                ],
                $groupAuthor = new GroupAuthor('group', new ArraySequence([])),
            ],
        ];
    }
}
