<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Promise\CallbackPromise;
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

    final private function getSubjects(array $subjects) : Sequence
    {
        if (empty($subjects)) {
            return new ArraySequence([]);
        }

        $subjects = array_map(function (array $subject) {
            return $subject['id'];
        }, $subjects);

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

        return new PromiseSequence($this->globalSubjectsCallback
            ->then(function (array $foundSubjects) use ($subjects) {
                return array_intersect_key($foundSubjects, array_flip($subjects));
            })
        );
    }
}
