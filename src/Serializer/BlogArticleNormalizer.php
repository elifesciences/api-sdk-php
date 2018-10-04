<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BlogArticleNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

    public function __construct(BlogClient $blogClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $article) : string {
                return $article['id'];
            },
            function (string $id) use ($blogClient) : PromiseInterface {
                return $blogClient->getArticle(
                    ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, BlogArticles::VERSION_BLOG_ARTICLE)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $class, $format = null, array $context = []) : BlogArticle
    {
        if (!empty($context['snippet'])) {
            $article = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['content'] = new PromiseSequence($article
                ->then(function (Result $article) {
                    return $article['content'];
                }));
        } else {
            $data['content'] = new ArraySequence($data['content']);
        }

        $data['content'] = $data['content']->map(function (array $block) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
        });

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            $context['snippet'] = true;

            return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
        }, $data['subjects'] ?? []));

        return new BlogArticle(
            $data['id'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            !empty($data['updated']) ? DateTimeImmutable::createFromFormat(DATE_ATOM, $data['updated']) : null,
            $data['impactStatement'] ?? null,
            $data['content'],
            $data['subjects']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            BlogArticle::class === $type
            ||
            is_a($type, Model::class, true) && 'blog-article' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param BlogArticle $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'blog-article';
        }

        if ($object->getUpdatedDate()) {
            $data['updated'] = $object->getUpdatedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getImpactStatement()) {
            $data['impactStatement'] = $object->getImpactStatement();
        }

        if (!$object->getSubjects()->isEmpty()) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
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
        return $data instanceof BlogArticle;
    }
}
