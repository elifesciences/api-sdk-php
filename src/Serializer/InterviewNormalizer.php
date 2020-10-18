<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Interviews;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\IntervieweeCvLine;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonDetails;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class InterviewNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(InterviewsClient $interviewsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $event) : string {
                return $event['id'];
            },
            function (string $id) use ($interviewsClient) : PromiseInterface {
                return $interviewsClient->getInterview(
                    ['Accept' => (string) new MediaType(InterviewsClient::TYPE_INTERVIEW, Interviews::VERSION_INTERVIEW)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : Interview
    {
        if (!empty($context['snippet'])) {
            $interview = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($interview
                ->then(function (Result $interview) {
                    return $interview['content'];
                }));

            $data['interviewee']['cv'] = new PromiseSequence($interview
                ->then(function (Result $interview) {
                    return $interview['interviewee']['cv'] ?? [];
                }));

            $data['image']['social'] = $interview
                ->then(function (Result $interview) {
                    return $interview['image']['social'] ?? null;
                });
        } else {
            $data['content'] = new ArraySequence($data['content']);

            $data['interviewee']['cv'] = new ArraySequence($data['interviewee']['cv'] ?? []);

            $data['image']['social'] = promise_for($data['image']['social'] ?? null);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['interviewee']['cv'] = $data['interviewee']['cv']->map(function (array $cvLine) {
            return new IntervieweeCvLine($cvLine['date'], $cvLine['text']);
        });

        if (false === empty($data['image']['thumbnail'])) {
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class,
                $format, $context);
        }

        $data['image']['social'] = $data['image']['social']
            ->then(function ($socialImage) use ($format, $context) {
                return false === empty($socialImage) ? $this->denormalizer->denormalize($socialImage, Image::class, $format, $context) : null;
            });

        return new Interview(
            $data['id'],
            new Interviewee(
                $this->denormalizer->denormalize($data['interviewee'], PersonDetails::class, $format, $context),
                $data['interviewee']['cv']
            ),
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['impactStatement'] ?? null,
            $data['image']['thumbnail'] ?? null,
            $data['image']['social'],
            $data['content']
        );
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
            'published' => $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'interview';
        }

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if ($object->getThumbnail()) {
            $data['image']['thumbnail'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        }

        if (empty($context['snippet'])) {
            if ($object->getSocialImage()) {
                $data['image']['social'] = $this->normalizer->normalize($object->getSocialImage(), $format, $context);
            }

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
