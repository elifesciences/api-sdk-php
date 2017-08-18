<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\JobAdvertsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Event;
use eLife\ApiSdk\Model\JobAdvert;
use eLife\ApiSdk\Model\Model;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class JobAdvertNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(JobAdvertsClient $jobAdvertsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $jobAdvert) : string {
                return $jobAdvert['id'];
            },
            function (string $id) use ($jobAdvertsClient) : PromiseInterface {
                return $jobAdvertsClient->getJobAdvert(
                    ['Accept' => new MediaType(JobAdvertsClient::TYPE_JOB_ADVERT, 1)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : JobAdvert
    {
        if (!empty($context['snippet'])) {
            $jobAdvert = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($jobAdvert
                ->then(function (Result $jobAdvert) {
                    return $jobAdvert['content'];
                }));
        } else {
            $data['content'] = new ArraySequence($data['content'] ?? []);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, ['snippet' => false] + $context);
        });

        return new JobAdvert(
            $data['id'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['closingDate']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['content']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            JobAdvert::class === $type
            ||
            Model::class === $type && 'job-advert' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param JobAdvert $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
            'closingDate' => $object->getClosingDate()->format(ApiSdk::DATE_FORMAT),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'job-advert';
        }

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (empty($context['snippet']) && $object->getContent()->notEmpty()) {
            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof JobAdvert;
    }
}
