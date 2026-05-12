<?php

namespace eLife\ApiSdk\Serializer;

use Symfony\Component\Serializer\Exception\ExceptionInterface;
use function array_merge;
use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\DigestsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Digests;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class DigestNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(DigestsClient $digestsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $digest) : string {
                return $digest['id'];
            },
            function (string $id) use ($digestsClient) : PromiseInterface {
                return $digestsClient->getDigest(
                    ['Accept' => (string) new MediaType(DigestsClient::TYPE_DIGEST, Digests::VERSION_DIGEST)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : Digest
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        if (!empty($context['snippet'])) {
            $digest = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($normalizationHelper->selectField($digest, 'content'));
            $data['relatedContent'] = new PromiseSequence($normalizationHelper->selectField($digest, 'relatedContent', []));
        } else {
            $data['content'] = new ArraySequence($data['content']);
            $data['relatedContent'] = new ArraySequence($data['relatedContent'] ?? []);
        }

        $data['subjects'] = $normalizationHelper->denormalizeArray($data['subjects'] ?? [], Subject::class, $context + ['snippet' => true]);

        $data['content'] = $normalizationHelper->denormalizeSequence($data['content'], Block::class, $context + ['snippet' => false]);
        $data['relatedContent'] = $normalizationHelper->denormalizeSequence($data['relatedContent'], Model::class, $context + ['snippet' => true]);

        if (false === empty($data['image']['social'])) {
            $data['image']['social'] = $this->denormalizer->denormalize($data['image']['social'], Image::class,
                $format, $context);
        }

        return new Digest(
            $data['id'],
            $data['title'],
            $data['impactStatement'] ?? null,
            $data['stage'],
            !empty($data['published']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']) : null,
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class, $format, $context + ['snippet' => false]),
            $data['image']['social'] ?? null,
            $data['subjects'],
            $data['content'],
            $data['relatedContent']
        );
    }


    /**
     * @param Digest $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $arr = [];
        if (!empty($context['type'])) {
            $arr['type'] = 'digest';
        }
        $arr['id'] = $data->getId();
        $arr['title'] = $data->getTitle();
        $arr['stage'] = $data->getStage();
        if ($data->getPublishedDate()) {
            $arr['published'] = $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT);
        }
        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }
        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }

        $arr['image']['thumbnail'] = $this->normalizer->normalize($data->getThumbnail(), $format, $context);

        if ($data->getSocialImage()) {
            $arr['image']['social'] = $this->normalizer->normalize($data->getSocialImage(), $format, $context);
        }
        if (!$data->getSubjects()->isEmpty()) {
            $arr['subjects'] = $normalizationHelper->normalizeSequenceToSnippets($data->getSubjects(), $context);
        }

        if (empty($context['snippet'])) {
            $typeContext = array_merge($context, ['type' => true]);

            $arr['content'] = $normalizationHelper->normalizeSequenceToSnippets($data->getContent(), $typeContext);
            $arr['relatedContent'] = $normalizationHelper->normalizeSequenceToSnippets($data->getRelatedContent(), $typeContext);
        }

        return $arr;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Digest::class === $type
            ||
            'digest' === ($data['type'] ?? 'unknown');
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Digest;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Digest::class => false,
            Model::class => false,
        ];
    }
}
