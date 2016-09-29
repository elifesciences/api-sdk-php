<?php

namespace test\eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\WebReference;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\Reference\WebReferenceNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class WebReferenceNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var WebReferenceNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new WebReferenceNormalizer();

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
    public function it_can_normalize_web_references($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $reference = new WebReference(ReferenceDate::fromString('2000'),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'title', 'http://www.example.com/');

        return [
            'web reference' => [$reference, null, true],
            'web reference with format' => [$reference, 'foo', true],
            'non-web reference' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_web_references(WebReference $reference, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($reference));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new WebReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'title',
                    'http://www.example.com/', 'website'),
                [
                    'type' => 'web',
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
                    'title' => 'title',
                    'uri' => 'http://www.example.com/',
                    'authorsEtAl' => true,
                    'website' => 'website',
                ],
            ],
            'minimum' => [
                new WebReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'title',
                    'http://www.example.com/'),
                [
                    'type' => 'web',
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
                    'title' => 'title',
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
    public function it_can_denormalize_web_references($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'web reference' => [[], WebReference::class, [], true],
            'reference that is a web page' => [['type' => 'web'], Reference::class, [], true],
            'reference that isn\'t a web page' => [['type' => 'foo'], Reference::class, [], false],
            'non-web reference' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider denormalizeProvider
     */
    public function it_denormalize_web_references(array $json, WebReference $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($json, WebReference::class));
    }

    public function denormalizeProvider() : array
    {
        return [
            'complete' => [
                [
                    'type' => 'web',
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
                    'title' => 'title',
                    'website' => 'website',
                    'uri' => 'http://www.example.com/',
                ],
                new WebReference(ReferenceDate::fromString('2000-01-01'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'title',
                    'http://www.example.com/', 'website'),
            ],
            'minimum' => [
                [
                    'type' => 'web',
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
                    'title' => 'title',
                    'uri' => 'http://www.example.com/',
                ],
                new WebReference(ReferenceDate::fromString('2000'),
                    [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'title',
                    'http://www.example.com/'),
            ],
        ];
    }
}
