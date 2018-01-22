<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;

final class Annotation implements Model, HasContent, HasId, HasIdentifier, HasCreatedDate, HasUpdatedDate
{
    private $id;
    private $access;
    private $parents;
    private $document;
    private $highlight;
    private $created;
    private $updated;
    private $content;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $access,
        AnnotationDocument $document,
        Sequence $parents,
        string $highlight = null,
        DateTimeImmutable $created,
        DateTimeImmutable $updated = null,
        Sequence $content
    ) {
        $this->id = $id;
        $this->access = $access;
        $this->document = $document;
        $this->parents = $parents;
        $this->highlight = $highlight;
        $this->created = $created;
        $this->updated = $updated;
        $this->content = $content;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::annotation($this->id);
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getAccess() : string
    {
        return $this->access;
    }

    /**
     * @return Sequence|string[]
     */
    public function getParents() : Sequence
    {
        return $this->parents;
    }

    public function getDocument() : AnnotationDocument
    {
        return $this->document;
    }

    /**
     * @return string|null
     */
    public function getHighlight()
    {
        return $this->highlight;
    }

    public function getCreatedDate() : DateTimeImmutable
    {
        return $this->created;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedDate()
    {
        return $this->updated;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getContent() : Sequence
    {
        return $this->content;
    }
}
