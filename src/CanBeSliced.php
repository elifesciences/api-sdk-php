<?php

namespace eLife\ApiSdk;

use LogicException;

trait CanBeSliced
{
    private $pages = [];
    private $pageBatch = 100;

    abstract public function slice(int $offset, int $length = null) : Collection;

    abstract public function count() : int;

    final private function getPage(int $page) : array
    {
        if (empty($this->pages)) {
            for ($i = 0; $i < $this->count(); ++$i) {
                if (0 === $i % $this->pageBatch) {
                    $this->pages[count($this->pages) + 1] = $this->slice($i, $this->pageBatch);
                }
            }
        }

        if (false === isset($this->pages[$page])) {
            throw new LogicException('Could not find page '.$page);
        }

        return iterator_to_array($this->pages[$page]);
    }
}
