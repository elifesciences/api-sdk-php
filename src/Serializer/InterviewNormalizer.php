<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;

final class InterviewNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $interviewsClient;
    private $found = [];
    private $globalCallback;

    public function __construct(InterviewsClient $interviewsClient)
    {
        $this->interviewsClient = $interviewsClient;
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Interview
    {
        if (!empty($context['snippet'])) {
            $interview = $this->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($interview
                ->then(function (Result $interview) {
                    return $interview['content'];
                }));

            $data['interviewee']['cv'] = new PromiseSequence($interview
                ->then(function (Result $interview) {
                    return $interview['interviewee']['cv'] ?? [];
                }));
        } else {
            $data['content'] = new ArraySequence($data['content']);

            $data['interviewee']['cv'] = new ArraySequence($data['interviewee']['cv'] ?? []);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['interviewee']['cv'] = $data['interviewee']['cv']->map(function (array $cvLine) {
            return new IntervieweeCvLine($cvLine['date'], $cvLine['text']);
        });

        return new Interview(
            $data['id'],
            new Interviewee(
                $this->denormalizer->denormalize($data['interviewee'], PersonDetails::class, $format, $context),
                $data['interviewee']['cv']
            ),
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            $data['impactStatement'] ?? null,
            $data['content']
        );
    }

    private function denormalizeSnippet(array $interview) : PromiseInterface
    {
        if (isset($this->found[$interview['id']])) {
            return $this->found[$interview['id']];
        }

        $this->found[$interview['id']] = null;

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                foreach ($this->found as $id => $interview) {
                    if (null === $interview) {
                        $this->found[$id] = $this->interviewsClient->getInterview(
                            ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)],
                            $id
                        );
                    }
                }

                $this->globalCallback = null;

                return all($this->found)->wait();
            });
        }

        return $this->globalCallback
            ->then(function (array $interviews) use ($interview) {
                return $interviews[$interview['id']];
            });
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            Interview::class === $type
            ||
            Model::class === $type && 'interview' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param Interview $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'interviewee' => $this->normalizer->normalize($object->getInterviewee()->getPerson(), $format, $context),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(DATE_ATOM),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'interview';
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (empty($context['snippet'])) {
            if (!$object->getInterviewee()->getCvLines()->isEmpty()) {
                $data['interviewee']['cv'] = $object->getInterviewee()->getCvLines()
                    ->map(function (IntervieweeCvLine $cvLine) {
                        return [
                            'date' => $cvLine->getDate(),
                            'text' => $cvLine->getText(),
                        ];
                    })->toArray();
            }

            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Interview;
    }
}
