<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticleVoR extends ArticleVersion
{
    private $impactStatement;
    private $keywords;
    private $digest;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        int $version,
        string $type,
        string $doi,
        string $authorLine,
        string $title,
        DateTimeImmutable $published,
        int $volume,
        string $elocationId,
        string $pdf = null,
        Collection $subjects = null,
        array $researchOrganisms,
        PromiseInterface $abstract,
        PromiseInterface $issue,
        PromiseInterface $copyright,
        Collection $authors,
        string $impactStatement = null,
        Collection $keywords,
        PromiseInterface $digest,
        Collection $content
    ) {
        parent::__construct($id, $version, $type, $doi, $authorLine, $title, $published, $volume, $elocationId, $pdf,
            $subjects, $researchOrganisms, $abstract, $issue, $copyright, $authors);

        $this->impactStatement = $impactStatement;
        $this->keywords = $keywords;
        $this->digest = $digest;
        $this->content = $content;
    }

    /**
     * @return string|null
     */
    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    public function getKeywords() : Collection
    {
        return $this->keywords;
    }

    /**
     * @return ArticleSection|null
     */
    public function getDigest()
    {
        return $this->digest->wait();
    }

    public function getContent() : Collection
    {
        return $this->content;
    }
}
