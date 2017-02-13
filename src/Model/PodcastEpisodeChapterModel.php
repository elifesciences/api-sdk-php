<?php

namespace eLife\ApiSdk\Model;

final class PodcastEpisodeChapterModel implements Model
{
    private $episode;
    private $chapter;

    /**
     * @internal
     */
    public function __construct(PodcastEpisode $episode, PodcastEpisodeChapter $chapter)
    {
        $this->episode = $episode;
        $this->chapter = $chapter;
    }

    public function getEpisode() : PodcastEpisode
    {
        return $this->episode;
    }

    public function getChapter() : PodcastEpisodeChapter
    {
        return $this->chapter;
    }
}
