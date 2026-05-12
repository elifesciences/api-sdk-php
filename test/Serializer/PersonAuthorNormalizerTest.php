<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class PersonAuthorNormalizerTest extends TestCase
{
    /** @var PersonAuthorNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new PersonAuthorNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new AddressNormalizer(),
            new ParagraphNormalizer(),
            new PersonDetailsNormalizer(),
            new PlaceNormalizer(),
        ]);
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_people($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $personAuthor = new PersonAuthor(new PersonDetails('preferred name', 'index name'));

        return [
            'person author' => [$personAuthor, null, true],
            'person author with format' => [$personAuthor, 'foo', true],
            'non-person author' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_person_authors(PersonAuthor $personAuthor, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($personAuthor));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new PersonAuthor(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'),
                    new ArraySequence([new Paragraph('biography')]), true, 'role',
                    ['additional information'], [new Place(['affiliation'])], 'competing interests', 'contribution',
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
            'minimum' => [
                new PersonAuthor(new PersonDetails('preferred name', 'index name')),
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                ],
            ],
        ];
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_people($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'person author' => [[], PersonAuthor::class, [], true],
            'author entry that is a person' => [['type' => 'person'], AuthorEntry::class, [], true],
            'author entry that isn\'t a person' => [['type' => 'foo'], AuthorEntry::class, [], false],
            'author that is a person' => [['type' => 'person'], Author::class, [], true],
            'author that isn\'t a person' => [['type' => 'foo'], Author::class, [], false],
            'non-person author' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_people(array $json, PersonAuthor $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, PersonAuthor::class));
    }

    public static function denormalizeProvider() : array
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
                    'biography' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'biography',
                        ],
                    ],
                    'deceased' => true,
                    'role' => 'role',
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
                new PersonAuthor(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'),
                    new ArraySequence([new Paragraph('biography')]), true, 'role',
                    ['additional information'], [new Place(['affiliation'])], 'competing interests', 'contribution',
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
