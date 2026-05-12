<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\PressPackagesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\PressPackages;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\MediaContact;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PressPackage;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class PressPackageNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(PressPackagesClient $pressPackagesClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $article) : string {
                return $article['id'];
            },
            function (string $id) use ($pressPackagesClient) : PromiseInterface {
                return $pressPackagesClient->getPackage(
                    ['Accept' => (string) new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE, PressPackages::VERSION_PRESS_PACKAGE)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : PressPackage
    {
        if (!empty($context['snippet'])) {
            $article = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['content'];
                }));

            $data['relatedContent'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['relatedContent'] ?? [];
                }));

            $data['mediaContacts'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['mediaContacts'] ?? [];
                }));

            $data['about'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['about'] ?? [];
                }));

            $data['image']['social'] = $article
                ->then(function (Result $article) {
                    return $article['image']['social'] ?? null;
                });
        } else {
            $data['content'] = new ArraySequence($data['content']);
            $data['relatedContent'] = new ArraySequence($data['relatedContent'] ?? []);
            $data['mediaContacts'] = new ArraySequence($data['mediaContacts'] ?? []);
            $data['about'] = new ArraySequence($data['about'] ?? []);
            $data['image']['social'] = promise_for($data['image']['social'] ?? null);
        }

        $data['image']['social'] = $data['image']['social']
            ->then(function ($socialImage) use ($format, $context) {
                return false === empty($socialImage) ? $this->denormalizer->denormalize($socialImage, Image::class, $format, $context) : null;
            });

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            return $this->denormalizer->denormalize($subject, Subject::class, $format, ['snippet' => true] + $context);
        }, $data['subjects'] ?? []));

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, ['snippet' => false] + $context);
        });

        $data['relatedContent'] = $data['relatedContent']->map(function ($eachContent) use ($format, $context) {
            return $this->denormalizer->denormalize(
                $eachContent,
                Model::class,
                $format,
                $context + ['snippet' => true]
            );
        });

        $data['mediaContacts'] = $data['mediaContacts']->map(function (array $mediaContact) use ($format, $context) {
            return $this->denormalizer->denormalize($mediaContact, MediaContact::class, $format, ['snippet' => false] + $context);
        });

        $data['about'] = $data['about']->map(function (array $block) use ($format, $context) {
            return $this->denormalizer->denormalize($block, Block::class, $format, ['snippet' => false] + $context);
        });

        return new PressPackage(
            $data['id'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['impactStatement'] ?? null,
            $data['image']['social'],
            $data['subjects'],
            $data['content'],
            $data['relatedContent'],
            $data['mediaContacts'],
            $data['about']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return PressPackage::class === $type
            ||
            is_a($type, Model::class, true) && 'press-package' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param PressPackage $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $normalizationHelper = new NormalizationHelper($this->normalizer, $this->denormalizer, $format);

        $arr = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'published' => $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
        ];

        if (!empty($context['type'])) {
            $arr['type'] = 'press-package';
        }

        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }

        if ($data->getSubjects()->notEmpty()) {
            $arr['subjects'] = $data->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                return $this->normalizer->normalize($subject, $format, ['snippet' => true] + $context);
            })->toArray();
        }

        if (empty($context['snippet'])) {
            if ($data->getSocialImage()) {
                $arr['image']['social'] = $this->normalizer->normalize($data->getSocialImage(), $format, $context);
            }

            $arr['content'] = $data->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })->toArray();

            if ($data->getRelatedContent()->notEmpty()) {
                $arr['relatedContent'] = $normalizationHelper->normalizeSequenceToSnippets($data->getRelatedContent(), $context + ['type' => true]);
            }

            if ($data->getMediaContacts()->notEmpty()) {
                $arr['mediaContacts'] = $data->getMediaContacts()->map(function (MediaContact $mediaContact) use ($format, $context) {
                    return $this->normalizer->normalize($mediaContact, $format, $context);
                })->toArray();

                $arr['about'] = $data->getAbout()->map(function (Block $block) use ($format, $context) {
                    return $this->normalizer->normalize($block, $format, $context);
                })->toArray();
            }
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof PressPackage;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PressPackage::class => false,
            Model::class => false,
        ];
    }
}
