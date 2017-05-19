<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class PodcastEpisodeNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(PodcastClient $podcastClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $episode) : int {
                return $episode['number'];
            },
            function (int $number) use ($podcastClient) : PromiseInterface {
                return $podcastClient->getEpisode(
                    ['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, 1)],
                    $number
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : PodcastEpisode
    {
        if (!empty($context['snippet'])) {
            $podcastEpisode = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['chapters'] = new PromiseSequence($podcastEpisode
                ->then(function (Result $podcastEpisode) {
                    return $podcastEpisode['chapters'];
                }));

            $data['image']['banner'] = $podcastEpisode
                ->then(function (Result $podcastEpisode) {
                    return $podcastEpisode['image']['banner'];
                });
        } else {
            $data['chapters'] = new ArraySequence($data['chapters']);

            $data['image']['banner'] = promise_for($data['image']['banner']);
        }

        $data['chapters'] = $data['chapters']
            ->map(function (array $chapter) use ($format, $context) {
                return new PodcastEpisodeChapter($chapter['number'], $chapter['title'], $chapter['time'],
                    $chapter['impactStatement'] ?? null,
                    new ArraySequence(array_map(function (array $item) use ($format, $context) {
                        $context['snippet'] = true;

                        return $this->denormalizer->denormalize($item, Model::class, $format, $context);
                    }, $chapter['content'] ?? [])));
            });

        $data['image']['banner'] = $data['image']['banner']
            ->then(function (array $banner) use ($format, $context) {
                return $this->denormalizer->denormalize($banner, Image::class, $format, $context);
            });

        $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class,
            $format, $context);

        $data['sources'] = array_map(function (array $source) {
            return new PodcastEpisodeSource($source['mediaType'], $source['uri']);
        }, $data['sources']);

        return new PodcastEpisode(
            $data['number'],
            $data['title'],
            $data['impactStatement'] ?? null,
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['image']['banner'],
            $data['image']['thumbnail'],
            $data['sources'],
            $data['chapters']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            PodcastEpisode::class === $type
            ||
            Model::class === $type && 'podcast-episode' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param PodcastEpisode $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $data = [
            'number' => $object->getNumber(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
            'image' => ['thumbnail' => $this->normalizer->normalize($object->getThumbnail(), $format, $context)],
            'sources' => array_map(function (PodcastEpisodeSource $source) {
                return [
                    'mediaType' => $source->getMediaType(),
                    'uri' => $source->getUri(),
                ];
            }, $object->getSources()),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'podcast-episode';
        }

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (empty($context['snippet'])) {
            $data['image']['banner'] = $this->normalizer->normalize($object->getBanner(), $format, $context);

            $data['chapters'] = $object->getChapters()->map(function (PodcastEpisodeChapter $chapter) use (
                $format,
                $context,
                $normalizationHelper
            ) {
                $typeContext = array_merge($context, ['type' => true]);

                $data = [
                    'number' => $chapter->getNumber(),
                    'title' => $chapter->getTitle(),
                    'time' => $chapter->getTime(),
                ];
                if ($chapter->getContent()->notEmpty()) {
                    $data['content'] = $normalizationHelper->normalizeSequenceToSnippets($chapter->getContent(), $typeContext);
                }

                if ($chapter->getImpactStatement()) {
                    $data['impactStatement'] = $chapter->getImpactStatement();
                }

                return $data;
            })->toArray();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PodcastEpisode;
    }
}
