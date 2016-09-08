<?php

namespace eLife\ApiSdk;

trait SlicedIterator
{
    use CanBeSliced;

    private $key = 1;

    final public function current()
    {
        $page = ceil($this->key / $this->pageBatch);
        $inPage = $this->key - ($page * $this->pageBatch) + $this->pageBatch - 1;

        return $this->getPage($page)[$inPage];
    }

    final public function next()
    {
        ++$this->key;
    }

    final public function key()
    {
        if ($this->key > $this->count()) {
            return null;
        }

        return $this->key;
    }

    final public function valid()
    {
        return $this->key <= $this->count();
    }

    final public function rewind()
    {
        $this->key = 1;
    }

    final private function resetIterator()
    {
        $this->rewind();
        $this->resetPages();
    }
}
