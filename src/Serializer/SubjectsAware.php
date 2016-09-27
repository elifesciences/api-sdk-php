<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\all;

trait SubjectsAware
{
    private $subjects;
    private $recordedSubjects = [];
    private $globalSubjectsCallback;

    final public function setSubjects(Subjects $subjects)
    {
        $this->subjects = $subjects;
    }

    final private function getSubjects($subjects) : PromiseInterface
    {
        if ($subjects instanceof ArrayCollection) {
            $subjects = $subjects->toArray();
        }
        $this->recordedSubjects = array_merge($this->recordedSubjects, $subjects);

        if (empty($this->globalSubjectsCallback)) {
            $this->globalSubjectsCallback = new CallbackPromise(function () {
                $subjects = [];
                foreach ($this->recordedSubjects as $subject) {
                    $subjects[$subject] = $this->subjects->get($subject);
                }

                $this->globalSubjectsCallback = null;

                return all($subjects)->wait();
            });
        }

        return new PromiseCollection($this->globalSubjectsCallback
            ->then(function (array $foundSubjects) use ($subjects) {
                return array_intersect_key($foundSubjects, array_flip($subjects));
            })
        );
    }
}
