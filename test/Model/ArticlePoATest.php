<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\ArticleVersion;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticlePoATest extends ArticleTest
{
    protected function createArticleVersion(
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
        Collection $authors
    ) : ArticleVersion {
        return new ArticlePoA($id, $version, $type, $doi, $authorLine, $title, $published, $volume, $elocationId, $pdf,
            $subjects, $researchOrganisms, $abstract, $issue, $copyright, $authors);
    }
}
