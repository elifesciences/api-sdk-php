<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiClient\PeopleClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PersonResearch;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;
use PHPUnit\Framework\Attributes\Before as Before;

final class PersonNormalizerTest extends ApiTestCase
{
    use NormalizerSamplesTestCase;

    /** @var PersonNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new PersonNormalizer(new PeopleClient($this->getHttpClient()));
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
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
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'title', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        return [
            'person' => [$person, null, true],
            'person with format' => [$person, 'foo', true],
            'non-person' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_people(Person $person, array $context, array $expected, callable $extra = null)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($person, null, $context));
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
            'person' => [[], Person::class, [], true],
            'non-person' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_people(
        Person $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Person::class, null, $context);

        $this->mockSubjectCall('subject1');

        $this->assertObjectsAreEqual($expected, $actual);
    }

    public static function normalizeProvider() : array
    {
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $subject = new Subject('subject1', 'Subject 1 name', Create::promiseFor('Subject subject1 impact statement'),
            new EmptySequence(), Create::promiseFor($banner), Create::promiseFor($thumbnail));

        return [
            'complete' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index', '0000-0002-1825-0097'),
                    Create::promiseFor('Given names'), Create::promiseFor('Surname'), 'senior-editor', 'Senior Editor', $thumbnail,
                    new ArraySequence([new Place(['affiliation'])]), Create::promiseFor(new PersonResearch(new ArraySequence([$subject]), ['Focus'], ['Organism'])),
                    new ArraySequence([new Paragraph('Person 1 profile text')]),
                    Create::promiseFor('Person 1 competing interests'), new ArraySequence([
                        new AccessControl('foo@example.com', AccessControl::ACCESS_PUBLIC),
                        new AccessControl('secret@example.com', AccessControl::ACCESS_RESTRICTED),
                    ])),
                [],
                [
                    'name' => [
                        'preferred' => 'Person 1 preferred',
                        'index' => 'Person 1 index',
                        'givenNames' => 'Given names',
                        'surname' => 'Surname',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'id' => 'person1',
                    'type' => [
                        'id' => 'senior-editor',
                        'label' => 'Senior Editor',
                    ],
                    'image' => [
                        'alt' => '',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                            'filename' => 'thumbnail.jpg',
                        ],
                        'size' => [
                            'width' => 140,
                            'height' => 140,
                        ],
                    ],
                    'affiliations' => [
                        [
                            'name' => ['affiliation'],
                        ],
                    ],
                    'research' => [
                        'expertises' => [
                            ['id' => 'subject1', 'name' => 'Subject 1 name'],
                        ],
                        'focuses' => ['Focus'],
                        'organisms' => ['Organism'],
                    ],
                    'profile' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Person 1 profile text',
                        ],
                    ],
                    'competingInterests' => 'Person 1 competing interests',
                    'emailAddresses' => [
                        [
                            'value' => 'foo@example.com',
                            'access' => 'public',
                        ],
                        [
                            'value' => 'secret@example.com',
                            'access' => 'restricted',
                        ],
                    ],
                ],
            ],
            'minimum' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index'), Create::promiseFor(null),
                    Create::promiseFor(null), 'senior-editor', 'Senior Editor', null, new EmptySequence(), Create::promiseFor(null),
                    new EmptySequence(), Create::promiseFor(null), new EmptySequence()),
                [],
                [
                    'name' => [
                        'preferred' => 'Person 1 preferred',
                        'index' => 'Person 1 index',
                    ],
                    'id' => 'person1',
                    'type' => [
                        'id' => 'senior-editor',
                        'label' => 'Senior Editor',
                    ],
                ],
            ],
            'complete snippet' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index', '0000-0002-1825-0097'),
                    Create::promiseFor('person1 given'), Create::promiseFor('person1 surname'), 'senior-editor', 'Senior Editor',
                    $thumbnail, new ArraySequence([new Place(['affiliation'])]),
                    Create::promiseFor(new PersonResearch(new ArraySequence([$subject]), ['Focus'], ['Organism'])),
                    new ArraySequence([new Paragraph('person1 profile text')]),
                    Create::promiseFor('person1 competing interests'), new ArraySequence([
                        new AccessControl('foo@example.com', AccessControl::ACCESS_PUBLIC),
                        new AccessControl('secret@example.com', AccessControl::ACCESS_RESTRICTED),
                    ])),
                ['snippet' => true],
                [
                    'name' => [
                        'preferred' => 'Person 1 preferred',
                        'index' => 'Person 1 index',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'id' => 'person1',
                    'type' => [
                        'id' => 'senior-editor',
                        'label' => 'Senior Editor',
                    ],
                    'image' => [
                        'alt' => '',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                        'source' => [
                            'mediaType' => 'image/jpeg',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                            'filename' => 'thumbnail.jpg',
                        ],
                        'size' => [
                            'width' => 140,
                            'height' => 140,
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockPersonCall(1, true);
                },
            ],
            'minimum snippet' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index'), Create::promiseFor(null),
                    Create::promiseFor(null), 'senior-editor',
                    'Senior Editor', null, new EmptySequence(), Create::promiseFor(null), new EmptySequence(), Create::promiseFor(null),
                    new EmptySequence()),
                ['snippet' => true],
                [
                    'name' => [
                        'preferred' => 'Person 1 preferred',
                        'index' => 'Person 1 index',
                    ],
                    'id' => 'person1',
                    'type' => [
                        'id' => 'senior-editor',
                        'label' => 'Senior Editor',
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockPersonCall(1);
                },
            ],
        ];
    }

    protected function class() : string
    {
        return Person::class;
    }

    protected static function samples(): \Generator
    {
        yield __DIR__.'/../../vendor/elife/api/dist/samples/person/v1/*.json';
        yield [__DIR__.'/../../vendor/elife/api/dist/samples/person-list/v1/*.json#items', ['snippet' => false]];
    }
}
