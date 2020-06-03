<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Tweet;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Serializer\Block\TweetNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use test\eLife\ApiSdk\TestCase;

final class TweetNormalizerTest extends TestCase
{
    /** @var TweetNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new TweetNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
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
    public function it_can_normalize_tweets($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $tweet = new Tweet('foo', new Date(2000), 'tweet', 'accountId', 'accountLabel');

        return [
            'tweet' => [$tweet, null, true],
            'tweet with format' => [$tweet, 'foo', true],
            'non-tweet' => [$this, null, false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_normalize_tweets(Tweet $tweet, array $expected)
    {
        $this->assertSame($expected, $this->normalizer->normalize($tweet));
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
    public function it_can_denormalize_tweets($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'tweet' => [[], Tweet::class, [], true],
            'block that is a tweet' => [['type' => 'tweet'], Block::class, [], true],
            'block that isn\'t a tweet' => [['type' => 'foo'], Block::class, [], false],
            'non-tweet' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     * @dataProvider normalizeProvider
     */
    public function it_denormalize_youtubes(Tweet $expected, array $json)
    {
        $this->assertObjectsAreEqual($expected, $this->normalizer->denormalize($json, Tweet::class));
    }

    public function normalizeProvider() : array
    {
        return [
            'complete' => [
                new Tweet(
                    'foo',
                    Date::fromString('2000-01-01'),
                    'tweet',
                    'accountId',
                    'accountLabel',
                    true,
                    true
                ),
                [
                    'type' => 'tweet',
                    'id' => 'foo',
                    'date' => '2000-01-01',
                    'text' => 'tweet',
                    'accountId' => 'accountId',
                    'accountLabel' => 'accountLabel',
                    'conversation' => true,
                    'mediaCard' => true,
                ],
            ],
            'minimum' => [
                new Tweet(
                    'foo',
                    Date::fromString('2000-01-01'),
                    'tweet',
                    'accountId',
                    'accountLabel'
                ),
                [
                    'type' => 'tweet',
                    'id' => 'foo',
                    'date' => '2000-01-01',
                    'text' => 'tweet',
                    'accountId' => 'accountId',
                    'accountLabel' => 'accountLabel',
                    'conversation' => false,
                    'mediaCard' => false,
                ],
            ],
        ];
    }
}
