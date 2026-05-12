<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\LabsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\LabsPosts;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsPost;
use eLife\ApiSdk\Model\Model;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class LabsPostNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(LabsClient $labsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $event) : string {
                return $event['id'];
            },
            function (string $id) use ($labsClient) : PromiseInterface {
                return $labsClient->getPost(
                    ['Accept' => (string) new MediaType(LabsClient::TYPE_POST, LabsPosts::VERSION_POST)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : LabsPost
    {
        if (!empty($context['snippet'])) {
            $post = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($post
                ->then(function (Result $post) {
                    return $post['content'];
                }));

            $data['image']['social'] = $post
                ->then(function (Result $post) {
                    return $article['image']['social'] ?? null;
                });
        } else {
            $data['content'] = new ArraySequence($data['content']);

            $data['image']['social'] = promise_for($data['image']['social'] ?? null);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class,
            $format, $context);

        $data['image']['social'] = $data['image']['social']
            ->then(function ($socialImage) use ($format, $context) {
                return false === empty($socialImage) ? $this->denormalizer->denormalize($socialImage, Image::class, $format, $context) : null;
            });

        return new LabsPost(
            $data['id'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['impactStatement'] ?? null,
            $data['image']['thumbnail'],
            $data['image']['social'],
            $data['content']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            LabsPost::class === $type
            ||
            Model::class === $type && 'labs-post' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param LabsPost $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'published' => $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
            'image' => [
                'thumbnail' => $this->normalizer->normalize($data->getThumbnail(), $format, $context),
            ],
        ];

        if (!empty($context['type'])) {
            $arr['type'] = 'labs-post';
        }

        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }

        if (empty($context['snippet'])) {
            $arr['content'] = $data->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();

            if ($data->getSocialImage()) {
                $arr['image']['social'] = $this->normalizer->normalize($data->getSocialImage(), $format, $context);
            }
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof LabsPost;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            LabsPost::class => false,
            Model::class => false,
        ];
    }
}
