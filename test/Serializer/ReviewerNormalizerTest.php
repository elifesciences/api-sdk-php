<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reviewer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\ReviewerNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\Builder;
use PHPUnit\Framework\Attributes\Before as Before;

final class ReviewerNormalizerTest extends TestCase
{
    /** @var ReviewerNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new ReviewerNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
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
        $reviewer = Builder::for(Reviewer::class)->__invoke();

        return [
            'reviewer' => [$reviewer, null, true],
            'reviewer with format' => [$reviewer, 'foo', true],
            'non-reviewer' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_reviewers(Reviewer $reviewer, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reviewer));
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
            'reviewer' => [[], Reviewer::class, [], true],
            'non-reviewer' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_denormalize_people(Reviewer $expected, array $json)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, Reviewer::class));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                Builder::for(Reviewer::class)
                    ->withPerson(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'))
                    ->withRole('role')
                    ->withAffiliations([new Place(['affiliation'])])
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
