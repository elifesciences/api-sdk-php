<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Question implements Block
{
    private $question;
    private $answer;

    /**
     * @internal
     */
    public function __construct(string $question, array $answer)
    {
        $this->question = $question;
        $this->answer = $answer;
    }

    public function getQuestion() : string
    {
        return $this->question;
    }

    /**
     * @return Block[]
     */
    public function getAnswer() : array
    {
        return $this->answer;
    }
}
