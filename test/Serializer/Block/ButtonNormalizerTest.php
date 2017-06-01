<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Button;
use eLife\ApiSdk\Serializer\Block\ButtonNormalizer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ButtonNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var ButtonNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new ButtonNormalizer();
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
    public function it_can_normalize_buttons($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $button = new Button('foo', 'http://www.example.com/');

        return [
            'button' => [$button, null, true],
            'button with format' => [$button, 'foo', true],
            'non-button' => [$this, null, false],
        ];
    }

    /**
     * @test
     */
    public function it_normalize_buttons()
    {
        $expected = [
            'type' => 'button',
            'text' => 'foo',
            'uri' => 'http://www.example.com/',
        ];

        $this->assertSame($expected, $this->normalizer->normalize(new Button('foo', 'http://www.example.com/')));
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
    public function it_can_denormalize_buttons($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'button' => [[], Button::class, [], true],
            'block that is a button' => [['type' => 'button'], Block::class, [], true],
            'block that isn\'t a button' => [['type' => 'foo'], Block::class, [], false],
            'non-button' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalize_buttons()
    {
        $json = [
            'type' => 'button',
            'text' => 'foo',
            'uri' => 'http://www.example.com/',
        ];

        $this->assertEquals(new Button('foo', 'http://www.example.com/'), $this->normalizer->denormalize($json, Button::class));
    }
}
