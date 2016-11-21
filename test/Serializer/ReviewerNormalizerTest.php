<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reviewer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\ReviewerNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use test\eLife\ApiSdk\Builder;

final class ReviewerNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var ReviewerNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ReviewerNormalizer();

        new Serializer([
            $this->normalizer,
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
        $reviewer = Builder::for(Reviewer::class)->__invoke();

        return [
            'reviewer' => [$reviewer, null, true],
            'reviewer with format' => [$reviewer, 'foo', true],
            'non-reviewer' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_reviewers(Reviewer $reviewer, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reviewer));
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
            'reviewer' => [[], Reviewer::class, [], true],
            'non-reviewer' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_people(Reviewer $expected, array $json)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Reviewer::class));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                Builder::for(Reviewer::class)
                    ->withPerson(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'))
                    ->withRole('role')
                    ->withAffiliations([new Place(null, null, ['affiliation'])])
                    ->__invoke(),
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                    'role' => 'role',
                    'orcid' => '0000-0002-1825-0097',
                    'affiliations' => [
                        [
                            'name' => ['affiliation'],
                        ],
                    ],
                ],
            ],
            'minimum' => [
                Builder::for(Reviewer::class)
                    ->withPerson(new PersonDetails('preferred name', 'index name'))
                    ->withRole('role')
                    ->__invoke(),
                [
                    'name' => [
                        'preferred' => 'preferred name',
                        'index' => 'index name',
                    ],
                    'role' => 'role',
                ],
            ],
        ];
    }
}
