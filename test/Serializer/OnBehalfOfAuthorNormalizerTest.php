<?php

namespace test\eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use eLife\ApiSdk\Serializer\OnBehalfOfAuthorNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class OnBehalfOfAuthorNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var OnBehalfOfAuthorNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new OnBehalfOfAuthorNormalizer();
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
    public function it_can_normalize_on_behalf_of_authors($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $onBehalfOfAuthor = new OnBehalfOfAuthor('foo');

        return [
            'on-behalf-of author' => [$onBehalfOfAuthor, null, true],
            'on-behalf-of author with format' => [$onBehalfOfAuthor, 'foo', true],
            'non-on-behalf-of author' => [$this, null, false],
        ];
    }

    /**
     * @test
     */
    public function it_normalize_on_behalf_of_authors()
    {
        $expected = [
            'type' => 'on-behalf-of',
            'onBehalfOf' => 'foo',
        ];

        $this->assertSame($expected, $this->normalizer->normalize(new OnBehalfOfAuthor('foo')));
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
    public function it_can_denormalize_on_behalf_of_authors($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'on-behalf-of author' => [[], OnBehalfOfAuthor::class, [], true],
            'author entry that is an on-behalf-of' => [['type' => 'on-behalf-of'], AuthorEntry::class, [], true],
            'author entry that isn\'t an on-behalf-of' => [['type' => 'foo'], AuthorEntry::class, [], false],
            'non-on-behalf-of author' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalize_on_behalf_of_authors()
    {
        $json = [
            'type' => 'on-behalf-of',
            'onBehalfOf' => 'foo',
        ];

        $expected = new OnBehalfOfAuthor('foo');

        $this->assertEquals($expected, $this->normalizer->denormalize($json, OnBehalfOfAuthor::class));
    }
}
