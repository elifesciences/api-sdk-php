<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\Reference\JournalReferenceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class JournalReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var JournalReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new JournalReferenceNormalizer();

        new Serializer([
            $this->normalizer,
            new PersonNormalizer(),
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
    public function it_can_normalize_journal_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new JournalReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'article title', new Place(null, null, ['journal']));

        return [
            'journal reference' => [$reference, null, true],
            'journal reference with format' => [$reference, 'foo', true],
            'non-journal reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_journal_references(JournalReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new JournalReference([new PersonAuthor(new Person('preferred name', 'index name'))], true,
                    'article title', new Place(null, null, ['journal']), 'volume', '10.1000/182', 18183754),
                [
                    'type' => 'journal',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'articleTitle' => 'article title',
                    'journal' => [
                        'name' => ['journal'],
                    ],
                    'authorsEtAl' => true,
                    'volume' => 'volume',
                    'doi' => '10.1000/182',
                    'pmid' => 18183754,
                ],
            ],
            'minimum' => [
                new JournalReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
                    'article title', new Place(null, null, ['journal'])),
                [
                    'type' => 'journal',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'articleTitle' => 'article title',
                    'journal' => [
                        'name' => ['journal'],
                    ],
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
    public function it_can_denormalize_journal_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'journal reference' => [[], JournalReference::class, [], true],
            'reference that is a journal' => [['type' => 'journal'], Reference::class, [], true],
            'reference that isn\'t a journal' => [['type' => 'foo'], Reference::class, [], false],
            'non-journal reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_journal_reference(array $json, JournalReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, JournalReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'journal',
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
                    'articleTitle' => 'article title',
                    'journal' => [
                        'name' => ['journal'],
                    ],
                    'volume' => 'volume',
                    'doi' => '10.1000/182',
                    'pmid' => 18183754,
                ],
                new JournalReference([new PersonAuthor(new Person('preferred name', 'index name'))], true,
                    'article title', new Place(null, null, ['journal']), 'volume', '10.1000/182', 18183754),
            ],
            'minimum' => [
                [
                    'type' => 'journal',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'articleTitle' => 'article title',
                    'journal' => [
                        'name' => ['journal'],
                    ],
                ],
                new JournalReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
                    'article title', new Place(null, null, ['journal'])),
            ],
        ];
    }
}
