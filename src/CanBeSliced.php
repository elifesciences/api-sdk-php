<?php

namespace eLife\ApiSdk;

use eLife\ApiSdk\Collection\Sequence;
use LogicException;

trait CanBeSliced
{
    use CanBeCounted;

    private $pages = [];
    private $pageBatch = 100;

    abstract public function slice(int $offset, int $length = null) : Sequence;

    final private function getPage(int $page) : Sequence
    {
        if (empty($this->pages[$page])) {
            $this->pages[$page] = $this->slice($this->pageBatch * ($page - 1), $this->pageBatch);
        }

        if (false === isset($this->pages[$page])) {
            throw new LogicException('Could not find page '.$page);
        }

        return $this->pages[$page];
    }

    final private function resetPages()
    {
        $this->pages = [];
        $this->pageBatch = 100;
    }
}
