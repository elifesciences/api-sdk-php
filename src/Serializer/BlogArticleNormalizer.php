<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\BlogClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class BlogArticleNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(BlogClient $blogClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $article) : string {
                return $article['id'];
            },
            function (string $id) use ($blogClient) : PromiseInterface {
                return $blogClient->getArticle(
                    ['Accept' => (string) new MediaType(BlogClient::TYPE_BLOG_ARTICLE, BlogArticles::VERSION_BLOG_ARTICLE)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : BlogArticle
    {
        if (!empty($context['snippet'])) {
            $article = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['content'];
                }));

            $data['image']['social'] = $article
                ->then(function (Result $article) {
                    return $article['image']['social'] ?? null;
                });
        } else {
            $data['content'] = new ArraySequence($data['content']);
            $data['image']['social'] = promise_for($data['image']['social'] ?? null);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            $context['snippet'] = true;

            return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
        }, $data['subjects'] ?? []));

        $data['image']['social'] = $data['image']['social']
            ->then(function ($socialImage) use ($format, $context) {
                return false === empty($socialImage) ? $this->denormalizer->denormalize($socialImage, Image::class, $format, $context) : null;
            });

        return new BlogArticle(
            $data['id'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['impactStatement'] ?? null,
            $data['image']['social'],
            $data['content'],
            $data['subjects']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            BlogArticle::class === $type
            ||
            is_a($type, Model::class, true) && 'blog-article' === ($data['type'] ?? 'unknown');
    }


    /**
     * @param BlogArticle $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'id' => $data->getId(),
            'title' => $data->getTitle(),
            'published' => $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
        ];

        if (!empty($context['type'])) {
            $arr['type'] = 'blog-article';
        }

        if ($data->getUpdatedDate()) {
            $arr['updated'] = $data->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getImpactStatement()) {
            $arr['impactStatement'] = $data->getImpactStatement();
        }

        if (!$data->getSubjects()->isEmpty()) {
            $arr['subjects'] = $data->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
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
        return $data instanceof BlogArticle;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            BlogArticle::class => false,
            Model::class => false,
        ];
    }
}
