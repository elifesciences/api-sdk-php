<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\Reference\ClinicalTrialReferenceNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;
use PHPUnit\Framework\Attributes\Before as Before;

final class ClinicalTrialReferenceNormalizerTest extends TestCase
{
    /** @var ClinicalTrialReferenceNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new ClinicalTrialReferenceNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new PersonDetailsNormalizer(),
            new PersonAuthorNormalizer(),
        ]);
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_clinical_trial_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $reference = new ClinicalTrialReference('id', Date::fromString('2000'), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/');

        return [
            'clinical trial reference' => [$reference, null, true],
            'clinical trial reference with format' => [$reference, 'foo', true],
            'non-clinical trial reference' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    #[DataProvider('normalizeProvider')]
    public function it_normalize_clinical_trial_references(ClinicalTrialReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public static function normalizeProvider() : array
    {
        return [
            'complete' => [
                new ClinicalTrialReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true,
                    ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/'),
                [
                    'type' => 'clinical-trial',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'authorsType' => 'authors',
                    'title' => 'clinical trial title',
                    'uri' => 'http://www.example.com/',
                    'discriminator' => 'a',
                    'authorsEtAl' => true,
                ],
            ],
            'minimum' => [
                new ClinicalTrialReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
                    ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/'),
                [
                    'type' => 'clinical-trial',
                    'id' => 'id',
                    'date' => '2000',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'authorsType' => 'authors',
                    'title' => 'clinical trial title',
                    'uri' => 'http://www.example.com/',
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
    public function it_can_denormalize_clinical_trial_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'clinical trial reference' => [[], ClinicalTrialReference::class, [], true],
            'reference that is a clinical trial' => [['type' => 'clinical-trial'], Reference::class, [], true],
            'reference that isn\'t a clinical trial' => [['type' => 'foo'], Reference::class, [], false],
            'non-clinical trial reference' => [[], self::class, [], false],
        ];
    }

    #[Test]
    #[DataProvider('denormalizeProvider')]
    public function it_denormalize_clinical_trial_references(array $json, ClinicalTrialReference $expected)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, ClinicalTrialReference::class));
    }

    public static function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'clinical-trial',
                    'id' => 'id',
                    'date' => '2000-01-01',
                    'discriminator' => 'a',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'authorsEtAl' => true,
                    'authorsType' => 'authors',
                    'title' => 'clinical trial title',
                    'uri' => 'http://www.example.com/',
                ],
                new ClinicalTrialReference('id', Date::fromString('2000-01-01'), 'a',
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true,
                    ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'clinical-trial',
                    'id' => 'id',
                    'date' => '2000',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'authorsType' => 'authors',
                    'title' => 'clinical trial title',
                    'uri' => 'http://www.example.com/',
                ],
                new ClinicalTrialReference('id', Date::fromString('2000'), null,
                    [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
                    ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/'),
            ],
        ];
    }
}
