<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Question;
use PHPUnit_Framework_TestCase;

final class QuestionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $question = new Question('question', [new Paragraph('answer')]);

        $this->assertInstanceOf(Block::class, $question);
    }

    /**
     * @test
     */
    public function it_has_a_question()
    {
        $question = new Question('question', [new Paragraph('answer')]);

        $this->assertSame('question', $question->getQuestion());
    }

    /**
     * @test
     */
    public function it_has_an_answer()
    {
        $question = new Question('question', [new Paragraph('answer')]);

        $this->assertEquals([new Paragraph('answer')], $question->getAnswer());
    }
}
