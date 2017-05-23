<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsPost;
use eLife\ApiSdk\Model\Model;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class LabsPostNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(LabsClient $labsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $event) : int {
                return $event['number'];
            },
            function (int $number) use ($labsClient) : PromiseInterface {
                return $labsClient->getPost(
                    ['Accept' => new MediaType(LabsClient::TYPE_POST, 1)],
                    $number
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : LabsPost
    {
        if (!empty($context['snippet'])) {
            $post = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($post
                ->then(function (Result $post) {
                    return $post['content'];
                }));
        } else {
            $data['content'] = new ArraySequence($data['content']);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class,
            $format, $context);

        return new LabsPost(
            $data['number'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['impactStatement'] ?? null,
            $data['image']['thumbnail'],
            $data['content']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            LabsPost::class === $type
            ||
            Model::class === $type && 'labs-post' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param LabsPost $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'number' => $object->getNumber(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
            'image' => [
                'thumbnail' => $this->normalizer->normalize($object->getThumbnail(), $format, $context),
            ],
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'labs-post';
        }

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (empty($context['snippet'])) {
            $data['content'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof LabsPost;
    }
}
