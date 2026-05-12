<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Button;
use eLife\ApiSdk\Serializer\Block\ButtonNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use PHPUnit\Framework\Attributes\Before as Before;

final class ButtonNormalizerTest extends TestCase
{
    /** @var ButtonNormalizer */
    private $normalizer;

    #[Before]
    protected function setUpNormalizer() : void
    {
        $this->normalizer = new ButtonNormalizer();
    }

    #[Test]
    public function it_is_a_normalizer()
    {
        $this->assertInstanceOf(NormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canNormalizeProvider')]
    public function it_can_normalize_buttons($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public static function canNormalizeProvider() : array
    {
        $button = new Button('foo', 'http://www.example.com/');

        return [
            'button' => [$button, null, true],
            'button with format' => [$button, 'foo', true],
            'non-button' => [new \stdClass(), null, false],
        ];
    }

    #[Test]
    public function it_normalize_buttons()
    {
        $expected = [
            'type' => 'button',
            'text' => 'foo',
            'uri' => 'http://www.example.com/',
        ];

        $this->assertSame($expected, $this->normalizer->normalize(new Button('foo', 'http://www.example.com/')));
    }

    #[Test]
    public function it_is_a_denormalizer()
    {
        $this->assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    #[Test]
    #[DataProvider('canDenormalizeProvider')]
    public function it_can_denormalize_buttons($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, null, $context));
    }

    public static function canDenormalizeProvider() : array
    {
        return [
            'button' => [[], Button::class, [], true],
            'block that is a button' => [['type' => 'button'], Block::class, [], true],
            'block that isn\'t a button' => [['type' => 'foo'], Block::class, [], false],
            'non-button' => [[], self::class, [], false],
        ];
    }

    #[Test]
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
