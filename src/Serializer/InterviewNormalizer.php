<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Person;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class InterviewNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Interview
    {
        $data['content'] = new PromiseCollection(promise_for($data['content'])
            ->then(function (array $blocks) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $blocks);
            }));

        if (!empty($data['interviewee']['cv'])) {
            $data['interviewee']['cv'] = new PromiseCollection(promise_for($data['interviewee']['cv'])
                ->then(function (array $cvLines) {
                    return array_map(function (array $cvLine) {
                        return new IntervieweeCvLine($cvLine['date'], $cvLine['text']);
                    }, $cvLines);
                }));
        }

        return new Interview(
            $data['id'],
            new Interviewee(
                $this->denormalizer->denormalize($data['interviewee'], Person::class, $format, $context),
                $data['interviewee']['cv'] ?? null
            ),
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            $data['impactStatement'] ?? null,
            $data['content']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return Interview::class === $type;
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

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (empty($context['snippet'])) {
            $data['interviewee'] = promise_for($data['interviewee'])
                ->then(function (array $interviewee) use ($object) {
                    if ($object->getInterviewee()->hasCvLines()) {
                        $interviewee['cv'] = $object->getInterviewee()->getCvLines()
                            ->map(function (IntervieweeCvLine $cvLine) {
                                return [
                                    'date' => $cvLine->getDate(),
                                    'text' => $cvLine->getText(),
                                ];
                            })->toArray();
                    }

                    return $interviewee;
                });

            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            });
        }

        return all($data)->wait();
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Interview;
    }
}
