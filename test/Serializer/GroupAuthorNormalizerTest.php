<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\GroupAuthorNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;

final class GroupAuthorNormalizerTest extends TestCase
{
    /** @var GroupAuthorNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new GroupAuthorNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new AddressNormalizer(),
            new ParagraphNormalizer(),
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
    public function it_can_normalize_group_authors($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $groupAuthor = new GroupAuthor('group', new EmptySequence());

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
                    new PersonAuthor(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'),
                        new ArraySequence([new Paragraph('biography')]), true, 'role',
                        ['additional information'], [new Place(['affiliation'])], 'competing interests', 'contribution',
                        ['foo@example.com'], [1], ['+12025550182;ext=555'],
                        [
                            $somewhere = Builder::for(Address::class)->sample('somewhere'),
                        ]),
                ]), ['sub-group' => [new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097')]],
                    ['additional information'], [new Place(['affiliation'])], 'competing interests', 'contribution',
                    ['foo@example.com'], [1], ['+12025550182;ext=555'],
                    [$somewhere]),
                [
                    'additionalInformation' => [
                        'additional information',
                    ],
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
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                            'orcid' => '0000-0002-1825-0097',
                            'biography' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'biography',
                                ],
                            ],
                            'deceased' => true,
                            'role' => 'role',
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
                new GroupAuthor('group', new EmptySequence()),
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
            'author that is a group' => [['type' => 'group'], Author::class, [], true],
            'author that isn\'t a group' => [['type' => 'foo'], Author::class, [], false],
            'non-group author' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_group_authors(array $json, GroupAuthor $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, GroupAuthor::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'group',
                    'name' => 'group',
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
                    'people' => [
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
                            'biography' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'biography',
                                ],
                            ],
                            'deceased' => true,
                            'role' => 'role',
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
                    new PersonAuthor(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'),
                        new ArraySequence([new Paragraph('biography')]), true, 'role',
                        ['additional information'], [new Place(['affiliation'])], 'competing interests', 'contribution',
                        ['foo@example.com'], [1], ['+12025550182;ext=555'],
                        [
                            $somewhere = Builder::for(Address::class)->sample('somewhere'),
                        ]),
                ]), ['sub-group' => [new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097')]],
                    ['additional information'], [new Place(['affiliation'])], 'competing interests', 'contribution',
                    ['foo@example.com'], [1], ['+12025550182;ext=555'],
                    [
                        $somewhere,
                    ]),
            ],
            'minimum' => [
                [
                    'type' => 'group',
                    'name' => 'group',
                ],
                $groupAuthor = new GroupAuthor('group', new EmptySequence()),
            ],
        ];
    }
}
