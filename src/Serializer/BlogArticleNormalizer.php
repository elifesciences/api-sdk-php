<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\all;

final class BlogArticleNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $blogClient;
    private $found = [];
    private $globalCallback;

    public function __construct(BlogClient $blogClient)
    {
        $this->blogClient = $blogClient;
    }

    public function denormalize($data, $class, $format = null, array $context = []) : BlogArticle
    {
        if (!empty($context['snippet'])) {
            $article = $this->denormalizeSnippet($data);

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
            DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s\Z', $data['published']),
            $data['impactStatement'] ?? null,
            $data['content'],
            $data['subjects']
        );
    }

    private function denormalizeSnippet(array $article) : PromiseInterface
    {
        if (isset($this->found[$article['id']])) {
            return $this->found[$article['id']];
        }

        $this->found[$article['id']] = null;

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                foreach ($this->found as $id => $article) {
                    if (null === $article) {
                        $this->found[$id] = $this->blogClient->getArticle(
                            ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 1)],
                            $id
                        );
                    }
                }

                $this->globalCallback = null;

                return all($this->found)->wait();
            });
        }

        return $this->globalCallback
            ->then(function (array $articles) use ($article) {
                return $articles[$article['id']];
            });
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            BlogArticle::class === $type
            ||
            Model::class === $type && 'blog-article' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param BlogArticle $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format('Y-m-d\TH:i:s\Z'),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'blog-article';
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
