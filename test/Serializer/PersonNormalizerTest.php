<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\PeopleClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PersonResearch;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

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
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'),
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
        $banner = new Image('',
            [new ImageSize('2:1', [900 => 'https://placehold.it/900x450', 1800 => 'https://placehold.it/1800x900'])]);
        $thumbnail = new Image('', [
            new ImageSize('16:9', [
                250 => 'https://placehold.it/250x141',
                500 => 'https://placehold.it/500x281',
            ]),
            new ImageSize('1:1', [
                '70' => 'https://placehold.it/70x70',
                '140' => 'https://placehold.it/140x140',
            ]),
        ]);
        $subject = new Subject('subject1', 'Subject 1 name', promise_for('Subject subject1 impact statement'),
            promise_for($banner), promise_for($thumbnail));

        return [
            'complete' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index', '0000-0002-1825-0097'),
                    'senior-editor', $thumbnail,
                    promise_for(new PersonResearch(new ArraySequence([$subject]), ['Focus'], ['Organism'])),
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
                    'type' => 'senior-editor',
                    'image' => [
                        'alt' => '',
                        'sizes' => [
                            '16:9' => [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                70 => 'https://placehold.it/70x70',
                                140 => 'https://placehold.it/140x140',
                            ],
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
                    null, promise_for(null), new EmptySequence(), promise_for(null)),
                [],
                [
                    'name' => [
                        'preferred' => 'Person 1 preferred',
                        'index' => 'Person 1 index',
                    ],
                    'id' => 'person1',
                    'type' => 'senior-editor',
                ],
            ],
            'complete snippet' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index', '0000-0002-1825-0097'),
                    'senior-editor', $thumbnail,
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
                    'type' => 'senior-editor',
                    'image' => [
                        'alt' => '',
                        'sizes' => [
                            '16:9' => [
                                250 => 'https://placehold.it/250x141',
                                500 => 'https://placehold.it/500x281',
                            ],
                            '1:1' => [
                                70 => 'https://placehold.it/70x70',
                                140 => 'https://placehold.it/140x140',
                            ],
                        ],
                    ],
                ],
                function (ApiTestCase $test) {
                    $test->mockPersonCall(1, true);
                },
            ],
            'minimum snippet' => [
                new Person('person1', new PersonDetails('Person 1 preferred', 'Person 1 index'), 'senior-editor',
                    null, promise_for(null), new EmptySequence(), promise_for(null)),
                ['snippet' => true],
                [
                    'name' => [
                        'preferred' => 'Person 1 preferred',
                        'index' => 'Person 1 index',
                    ],
                    'id' => 'person1',
                    'type' => 'senior-editor',
                ],
                function (ApiTestCase $test) {
                    $test->mockPersonCall(1);
                },
            ],
        ];
    }
}
