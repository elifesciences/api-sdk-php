<?php

namespace eLife\ApiSdk\Model;

use InvalidArgumentException;

final class Identifier
{
    private $type;
    private $id;

    private function __construct(string $type, string $id)
    {
        $this->type = $type;
        $this->id = $id;
    }

    public static function fromString(string $string) : Identifier
    {
        preg_match('~^(annual-report|article|blog-article|collection|event|interview|labs-post|person|podcast-episode|press-package|profile|subject)/([a-z0-9-]+)$~', $string, $matches);

        if (empty($matches[1]) || empty($matches[2])) {
            throw new InvalidArgumentException("Invalid identifier '$string'");
        }

        return new self($matches[1], $matches[2]);
    }

    public static function annualReport(int $year) : Identifier
    {
        return new self('annual-report', $year);
    }

    public static function article(string $id) : Identifier
    {
        return new self('article', $id);
    }

    public static function blogArticle(string $id) : Identifier
    {
        return new self('blog-article', $id);
    }

    public static function collection(string $id) : Identifier
    {
        return new self('collection', $id);
    }

    public static function event(string $id) : Identifier
    {
        return new self('event', $id);
    }

    public static function interview(string $id) : Identifier
    {
        return new self('interview', $id);
    }

    public static function jobAdvert(string $id) : Identifier
    {
        return new self('job-advert', $id);
    }

    public static function labsPost(string $id) : Identifier
    {
        return new self('labs-post', $id);
    }

    public static function person(string $id) : Identifier
    {
        return new self('person', $id);
    }

    public static function podcastEpisode(int $number) : Identifier
    {
        return new self('podcast-episode', $number);
    }

    public static function pressPackage(string $id) : Identifier
    {
        return new self('press-package', $id);
    }

    public static function profile(string $id) : Identifier
    {
        return new self('profile', $id);
    }

    public static function subject(string $id) : Identifier
    {
        return new self('subject', $id);
    }

    public function __toString()
    {
        return "{$this->type}/{$this->id}";
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getId()
    {
        return $this->id;
    }
}
