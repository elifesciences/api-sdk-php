<?php

namespace test\eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Question;
use eLife\ApiSdk\Serializer\Block\ParagraphNormalizer;
use eLife\ApiSdk\Serializer\Block\QuestionNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class QuestionNormalizerTest extends PHPUnit_Framework_TestCase
{
    /** @var QuestionNormalizer */
    private $normalizer;

    /**
     * @before
     */
    protected function setUpNormalizer()
    {
        $this->normalizer = new QuestionNormalizer();

        new NormalizerAwareSerializer([
            $this->normalizer,
            new ParagraphNormalizer(),
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
    public function it_can_normalize_questions($data, $format, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsNormalization($data, $format));
    }

    public function canNormalizeProvider() : array
    {
        $question = new Question('question', [new Paragraph('answer')]);

        return [
            'question' => [$question, null, true],
            'question with format' => [$question, 'foo', true],
            'non-question' => [$this, null, false],
        ];
    }

    /**
     * @test
     */
    public function it_normalize_questions()
    {
        $question = new Question('question', [new Paragraph('answer')]);
        $expected = [
            'type' => 'question',
            'question' => 'question',
            'answer' => [
                [
                    'type' => 'paragraph',
                    'text' => 'answer',
                ],
            ],
        ];

        $this->assertSame($expected, $this->normalizer->normalize($question));
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
    public function it_can_denormalize_questions($data, $format, array $context, bool $expected)
    {
        $this->assertSame($expected, $this->normalizer->supportsDenormalization($data, $format, $context));
    }

    public function canDenormalizeProvider() : array
    {
        return [
            'question' => [[], Question::class, [], true],
            'block that is a question' => [['type' => 'question'], Block::class, [], true],
            'block that isn\'t a question' => [['type' => 'foo'], Block::class, [], false],
            'non-question' => [[], get_class($this), [], false],
        ];
    }

    /**
     * @test
     */
    public function it_denormalize_questions()
    {
        $json = [
            'type' => 'question',
            'question' => 'question',
            'answer' => [
                [
                    'type' => 'paragraph',
                    'text' => 'answer',
                ],
            ],
        ];
        $expected = new Question('question', [new Paragraph('answer')]);

        $this->assertEquals($expected, $this->normalizer->denormalize($json, Question::class));
    }
}
