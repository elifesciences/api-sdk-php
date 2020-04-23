<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasId;

final class Tweet implements HasId
{
    private $id;
    private $date;
    private $text;
    private $accountId;
    private $accountLabel;
    private $conversation;
    private $mediaCard;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        Date $date,
        array $text,
        string $accountId,
        string $accountLabel,
        bool $conversation = false,
        bool $mediaCard = false
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->text = $text;
        $this->accountId = $accountId;
        $this->accountLabel = $accountLabel;
        $this->conversation = $conversation;
        $this->mediaCard = $mediaCard;
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getDate() : Date
    {
        return $this->date;
    }

    /**
     * @return Block[]
     */
    public function getText() : array
    {
        return $this->text;
    }

    public function getAccountId() : string
    {
        return $this->accountId;
    }

    public function getAccountLabel() : string
    {
        return $this->accountLabel;
    }

    public function isConversation() : bool
    {
        return $this->conversation;
    }

    public function isMediaCard() : bool
    {
        return $this->mediaCard;
    }
}
