<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiClient\ApiClient\ProfilesClient;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Profile;
use eLife\ApiSdk\Serializer\ProfileNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\ApiTestCase;
use test\eLife\ApiSdk\Builder;

final class ProfileNormalizerTest extends ApiTestCase
{
    /** @var ProfileNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $apiSdk = new ApiSdk($this->getHttpClient());
        $this->normalizer = new ProfileNormalizer(new ProfilesClient($this->getHttpClient()));
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
    public function it_can_normalize_profiles($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $profile = Builder::for(Profile::class)->__invoke();

        return [
            'profile' => [$profile, null, true],
            'profile with format' => [$profile, 'foo', true],
            'non-profile' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_profiles(Profile $profile, array $context, array $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($profile, null, $context));
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
    public function it_can_denormalize_profiles($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'profile' => [[], Profile::class, [], true],
            'non-profile' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_profiles(
        Profile $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        if ($extra) {
            call_user_func($extra, $this);
        }

        $actual = $this->normalizer->denormalize($json, Profile::class, null, $context);

        $this->mockSubjectCall('subject1');

        $this->assertObjectsAreEqual($expected, $actual);
    }

    /**
     * Remove after https://github.com/elifesciences/api-raml/pull/204 is merged.
     *
     * @test
     * @dataProvider normalizeProviderBackwardCompatibility
     */
    public function it_denormalize_profiles_from_api_responses_without_access_control(
        Profile $expected,
        array $context,
        array $json,
        callable $extra = null
    ) {
        $this->it_denormalize_profiles($expected, $context, $json, $extra);
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Profile(
                    'profile1',
                    new PersonDetails('Profile 1 preferred', 'Profile 1 index', '0000-0002-1825-0097'),
                    new ArraySequence([
                        new AccessControl(new Place(['affiliation'])),
                    ]),
                    new ArraySequence([
                        new AccessControl('foo@example.com', 'public'),
                        new AccessControl('secret@example.com', 'restricted'),
                    ])
                ),
                [],
                [
                    'name' => [
                        'preferred' => 'Profile 1 preferred',
                        'index' => 'Profile 1 index',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'id' => 'profile1',
                    'affiliations' => [
                        [
                            'value' => [
                                'name' => ['affiliation'],
                            ],
                            'access' => 'public',
                        ],
                    ],
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
                new Profile('profile1', new PersonDetails('Profile 1 preferred', 'Profile 1 index'), new EmptySequence(), new EmptySequence()),
                [],
                [
                    'name' => [
                        'preferred' => 'Profile 1 preferred',
                        'index' => 'Profile 1 index',
                    ],
                    'id' => 'profile1',
                ],
            ],
            'complete snippet' => [
                new Profile(
                    'profile1',
                    new PersonDetails('Profile 1 preferred', 'Profile 1 index', '0000-0002-1825-0097'),
                    new ArraySequence([
                        new AccessControl(new Place(['affiliation'])),
                    ]),
                    new ArraySequence([
                        new AccessControl('foo@example.com'),
                        new AccessControl('secret@example.com', 'restricted'),
                    ])
                ),
                ['snippet' => true],
                [
                    'name' => [
                        'preferred' => 'Profile 1 preferred',
                        'index' => 'Profile 1 index',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'id' => 'profile1',
                ],
                function (ApiTestCase $test) {
                    $test->mockProfileCall(1, true);
                },
            ],
            'minimum snippet' => [
                new Profile('profile1', new PersonDetails('Profile 1 preferred', 'Profile 1 index'), new EmptySequence(), new EmptySequence()),
                ['snippet' => true],
                [
                    'name' => [
                        'preferred' => 'Profile 1 preferred',
                        'index' => 'Profile 1 index',
                    ],
                    'id' => 'profile1',
                ],
                function (ApiTestCase $test) {
                    $test->mockProfileCall(1);
                },
            ],
        ];
    }

    public static function normalizeProviderBackwardCompatibility() : array
    {
        return [
            'complete' => [
                new Profile(
                    'profile1',
                    new PersonDetails('Profile 1 preferred', 'Profile 1 index', '0000-0002-1825-0097'),
                    new ArraySequence([
                        new AccessControl(new Place(['affiliation'])),
                    ]),
                    new ArraySequence([
                        new AccessControl('foo@example.com'),
                        new AccessControl('secret@example.com'),
                    ])
                ),
                [],
                [
                    'name' => [
                        'preferred' => 'Profile 1 preferred',
                        'index' => 'Profile 1 index',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'id' => 'profile1',
                    'affiliations' => [
                        [
                            'name' => ['affiliation'],
                        ],
                    ],
                    'emailAddresses' => [
                        'foo@example.com',
                        'secret@example.com',
                    ],
                ],
            ],
            'complete snippet' => [
                new Profile(
                    'profile1',
                    new PersonDetails('Profile 1 preferred', 'Profile 1 index', '0000-0002-1825-0097'),
                    new ArraySequence([
                        new AccessControl(new Place(['affiliation'])),
                    ]),
                    new ArraySequence([
                        new AccessControl('foo@example.com', 'public'),
                        // note if it was in the old response, we have to assume it's public
                        new AccessControl('secret@example.com', 'public'),
                    ])
                ),
                ['snippet' => true],
                [
                    'name' => [
                        'preferred' => 'Profile 1 preferred',
                        'index' => 'Profile 1 index',
                    ],
                    'orcid' => '0000-0002-1825-0097',
                    'id' => 'profile1',
                ],
                function (ApiTestCase $test) {
                    $test->disableValidationOfResponses();
                    $test->setUpNormalizer();
                    $test->mockProfileCall(1, $complete = true, $isSnippet = false, $isOld = true);
                },
            ],
        ];
    }
}
