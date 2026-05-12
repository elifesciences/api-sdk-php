<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\PodcastClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\PodcastEpisodeChapter;
use eLife\ApiSdk\Model\PodcastEpisodeSource;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class PodcastEpisodeNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(PodcastClient $podcastClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $episode) : int {
                return $episode['number'];
            },
            function (int $number) use ($podcastClient) : PromiseInterface {
                return $podcastClient->getEpisode(
                    ['Accept' => (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, PodcastEpisodes::VERSION_PODCAST_EPISODE)],
                    $number
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : PodcastEpisode
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

            $data['image']['social'] = $podcastEpisode
                ->then(function (Result $podcastEpisode) {
                    return $podcastEpisode['image']['social'] ?? null;
                });
        } else {
            $data['chapters'] = new ArraySequence($data['chapters']);

            $data['image']['banner'] = promise_for($data['image']['banner']);

            $data['image']['social'] = promise_for($data['image']['social'] ?? null);
        }

        $data['chapters'] = $data['chapters']
            ->map(function (array $chapter) use ($format, $context) {
                return new PodcastEpisodeChapter($chapter['number'], $chapter['title'], $chapter['longTitle'] ?? null, $chapter['time'],
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

        $data['image']['social'] = $data['image']['social']
            ->then(function ($socialImage) use ($format, $context) {
                return false === empty($socialImage) ? $this->denormalizer->denormalize($socialImage, Image::class, $format, $context) : null;
            });

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
            $data['image']['social'],
            $data['sources'],
            $data['chapters']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            PodcastEpisode::class === $type
            ||
            Model::class === $type && 'podcast-episode' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param PodcastEpisode $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $arr = [
            'number' => $data->getNumber(),
            'title' => $data->getTitle(),
            'published' => $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
            'image' => ['thumbnail' => $this->normalizer->normalize($data->getThumbnail(), $format, $context)],
            'sources' => array_map(function (PodcastEpisodeSource $source) {
                return [
                    'mediaType' => $source->getMediaType(),
                    'uri' => $source->getUri(),
                ];
            }, $data->getSources()),
        ];

        if (!empty($context['type'])) {
            $arr['type'] = 'podcast-episode';
        }

        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }

        if (empty($context['snippet'])) {
            $arr['image']['banner'] = $this->normalizer->normalize($data->getBanner(), $format, $context);

            if ($data->getSocialImage()) {
                $arr['image']['social'] = $this->normalizer->normalize($data->getSocialImage(), $format, $context);
            }

            $arr['chapters'] = $data->getChapters()->map(function (PodcastEpisodeChapter $chapter) use (
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

                if ($chapter->getLongTitle()) {
                    $data['longTitle'] = $chapter->getLongTitle();
                }

                if ($chapter->getContent()->notEmpty()) {
                    $data['content'] = $normalizationHelper->normalizeSequenceToSnippets($chapter->getContent(), $typeContext);
                }

                if ($chapter->getImpactStatement()) {
                    $data['impactStatement'] = $chapter->getImpactStatement();
                }

                return $data;
            })->toArray();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof PodcastEpisode;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PodcastEpisode::class => false,
            Model::class => false,
        ];
    }
}
