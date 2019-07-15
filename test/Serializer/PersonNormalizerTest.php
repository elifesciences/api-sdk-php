<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\PeopleClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PersonResearch;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class PersonNormalizerTest extends ApiTestCase
{
    /** @var PersonNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new PersonNormalizer(new PeopleClient($this->getHttpClient()));
        $this->normalizer->setNormalizer($apiSdk->getSerializer());
        $this->normalizer->setDenormalizer($apiSdk->getSerializer());
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
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', 'title', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));

        return [
            'person' => [$person, null, true],
            'person with format' => [$person, 'foo', true],
            'non-person' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_people(Person $person, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($person, null, $context));
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
            'person' => [[], Person::class, [], true],
            'non-person' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
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

    public function normalizeProvider() : array
    {
        $banner = Builder::for(Image::class)->sample('banner');
        $thumbnail = Builder::for(Image::class)->sample('thumbnail');
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
            new EmptySequence(), promise_for($banner), promise_for($thumbnail));

        return [
            'complete' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index', '0000-0002-1825-0097'),
                    'senior-editor', 'Senior Editor', $thumbnail,
                    new ArraySequence([new Place(['affiliation'])]), promise_for(new PersonResearch(new ArraySequence([$subject]), ['Focus'], ['Organism'])),
                    new ArraySequence([new Paragraph('Person 1 profile text')]),
                    promise_for('Person 1 competing interests')),
                [],
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
                ],
            ],
            'minimum' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index'), 'senior-editor',
                    'Senior Editor', null, new EmptySequence(), promise_for(null), new EmptySequence(), promise_for(null)),
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
                    'senior-editor', 'Senior Editor', $thumbnail,
                    new ArraySequence([new Place(['affiliation'])]),
                    promise_for(new PersonResearch(new ArraySequence([$subject]), ['Focus'], ['Organism'])),
                    new ArraySequence([new Paragraph('person1 profile text')]),
                    promise_for('person1 competing interests')),
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
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index'), 'senior-editor',
                    'Senior Editor', null, new EmptySequence(), promise_for(null), new EmptySequence(), promise_for(null)),
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
}
