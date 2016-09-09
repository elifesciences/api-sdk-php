<?php

namespace eLife\ApiSdk\Model;

final class IntervieweeCvLine
{
    private $date;
    private $text;

    public function __construct(string $date, string $text)
    {
        $this->date = $date;
        $this->text = $text;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getText()
    {
        return $this->text;
    }
}
