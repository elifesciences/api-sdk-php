<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\InterviewsClient;
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
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class InterviewNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

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

    /**
     * @param $data
     * @param $type
     * @param $format
     * @param array $context
     * @return Interview
     * @throws ExceptionInterface
     */
    public function denormalize($data, $type, $format = null, array $context = []) : Interview
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

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Interview::class === $type
            ||
            Model::class === $type && 'interview' === ($data['type'] ?? 'unknown');
    }


    /**
     * @param Interview $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'id' => $data->getId(),
            'interviewee' => $this->normalizer->normalize($data->getInterviewee()->getPerson(), $format, $context),
            'title' => $data->getTitle(),
            'published' => $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
        ];

        if (!empty($context['type'])) {
            $arr['type'] = 'interview';
        }

        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }

        if ($data->getThumbnail()) {
            $arr['image']['thumbnail'] = $this->normalizer->normalize($data->getThumbnail(), $format, $context);
        }

        if (empty($context['snippet'])) {
            if ($data->getSocialImage()) {
                $arr['image']['social'] = $this->normalizer->normalize($data->getSocialImage(), $format, $context);
            }

            if (!$data->getInterviewee()->getCvLines()->isEmpty()) {
                $arr['interviewee']['cv'] = $data->getInterviewee()->getCvLines()
                    ->map(function (IntervieweeCvLine $cvLine) {
                        return [
                            'date' => $cvLine->getDate(),
                            'text' => $cvLine->getText(),
                        ];
                    })->toArray();
            }

            $arr['content'] = $data->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Interview;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Interview::class => false,
            Model::class => false,
        ];
    }
}
