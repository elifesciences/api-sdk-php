<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\Reference\ClinicalTrialReferenceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class ClinicalTrialReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var ClinicalTrialReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ClinicalTrialReferenceNormalizer();

        new Serializer([
            $this->normalizer,
            new PersonNormalizer(),
            new PersonAuthorNormalizer(),
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
    public function it_can_normalize_clinical_trial_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new ClinicalTrialReference(ReferenceDate::fromString('2000'),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/');

        return [
            'clinical trial reference' => [$reference, null, true],
            'clinical trial reference with format' => [$reference, 'foo', true],
            'non-clinical trial reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_clinical_trial_references(ClinicalTrialReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new ClinicalTrialReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], true,
                    ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/'),
                [
                    'type' => 'clinical-trial',
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
                    'authorsEtAl' => true,
                ],
            ],
            'minimum' => [
                new ClinicalTrialReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false,
                    ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/'),
                [
                    'type' => 'clinical-trial',
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
    public function it_can_denormalize_clinical_trial_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'clinical trial reference' => [[], ClinicalTrialReference::class, [], true],
            'reference that is a clinical trial' => [['type' => 'clinical-trial'], Reference::class, [], true],
            'reference that isn\'t a clinical trial' => [['type' => 'foo'], Reference::class, [], false],
            'non-clinical trial reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_clinical_trial_references(array $json, ClinicalTrialReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, ClinicalTrialReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'clinical-trial',
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
                    'authorsEtAl' => true,
                    'authorsType' => 'authors',
                    'title' => 'clinical trial title',
                    'uri' => 'http://www.example.com/',
                ],
                new ClinicalTrialReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], true,
                    ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/'),
            ],
            'minimum' => [
                [
                    'type' => 'clinical-trial',
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
                new ClinicalTrialReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false,
                    ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'clinical trial title', 'http://www.example.com/'),
            ],
        ];
    }
}
