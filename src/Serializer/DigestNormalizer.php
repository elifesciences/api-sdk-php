<?php

namespace eLife\ApiSdk\Serializer;

use function array_merge;
use DateTimeImmutable;
use eLife\ApiClient\ApiClient\DigestsClient;
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

final class DigestNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

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

    public function denormalize($data, $class, $format = null, array $context = []) : Digest
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

        return new Digest(
            $data['id'],
            $data['title'],
            $data['impactStatement'] ?? null,
            $data['stage'],
            !empty($data['published']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']) : null,
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class, $format, $context + ['snippet' => false]),
            $data['subjects'],
            $data['content'],
            $data['relatedContent']
        );
    }

    /**
     * @param Digest $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [];
        if (!empty($context['type'])) {
            $data['type'] = 'digest';
        }
        $data['id'] = $object->getId();
        $data['title'] = $object->getTitle();
        $data['stage'] = $object->getStage();
        if ($object->getPublishedDate()) {
            $data['published'] = $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT);
        }
        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }
        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        $data['image']['thumbnail'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        if (!$object->getSubjects()->isEmpty()) {
            $data['subjects'] = $normalizationHelper->normalizeSequenceToSnippets($object->getSubjects(), $context);
        }

        if (empty($context['snippet'])) {
            $typeContext = array_merge($context, ['type' => true]);

            $data['content'] = $normalizationHelper->normalizeSequenceToSnippets($object->getContent(), $typeContext);
            $data['relatedContent'] = $normalizationHelper->normalizeSequenceToSnippets($object->getRelatedContent(), $typeContext);
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            Digest::class === $type
            ||
            'digest' === ($data['type'] ?? 'unknown');
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Digest;
    }
}
