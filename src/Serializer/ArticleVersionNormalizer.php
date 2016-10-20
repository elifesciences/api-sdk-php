<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Copyright;
use eLife\ApiSdk\Model\Subject;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

abstract class ArticleVersionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use SubjectsAware;

    final public function denormalize($data, $class, $format = null, array $context = []) : ArticleVersion
    {
        $data['abstract'] = promise_for($data['abstract'] ?? null)
            ->then(function ($abstract) use ($format, $context) {
                if (empty($abstract)) {
                    return null;
                }

                return new ArticleSection(
                    new ArraySequence(array_map(function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    }, $abstract['content'])),
                    $abstract['doi'] ?? null
                );
            });

        $data['authors'] = new PromiseSequence(promise_for($data['authors'])
            ->then(function (array $authors) use ($format, $context) {
                return array_map(function (array $author) use ($format, $context) {
                    return $this->denormalizer->denormalize($author, AuthorEntry::class, $format, $context);
                }, $authors);
            }));

        $data['copyright'] = promise_for($data['copyright'])
            ->then(function (array $copyright) {
                return new Copyright($copyright['license'], $copyright['statement'], $copyright['holder'] ?? null);
            });

        $data['issue'] = promise_for($data['issue'] ?? null);

        $data['subjects'] = $this->getSubjects($data['subjects'] ?? []);

        return $this->denormalizeArticle($data, $format, $context);
    }

    /**
     * @param ArticleVersion $object
     */
    final public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'version' => $object->getVersion(),
            'type' => $object->getType(),
            'doi' => $object->getDoi(),
            'authorLine' => $object->getAuthorLine(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(DATE_ATOM),
            'statusDate' => $object->getStatusDate()->format(DATE_ATOM),
            'volume' => $object->getVolume(),
            'elocationId' => $object->getElocationId(),
        ];

        if ($object->getTitlePrefix()) {
            $data['titlePrefix'] = $object->getTitlePrefix();
        }

        if ($object->getPdf()) {
            $data['pdf'] = $object->getPdf();
        }

        if (count($object->getSubjects()) > 0) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) {
                return ['id' => $subject->getId(), 'name' => $subject->getName()];
            })->toArray();
        }

        if (!empty($object->getResearchOrganisms())) {
            $data['researchOrganisms'] = $object->getResearchOrganisms();
        }

        if (empty($context['snippet'])) {
            $data['copyright'] = [
                'license' => $object->getCopyright()->getLicense(),
                'statement' => $object->getCopyright()->getStatement(),
            ];

            if ($object->getCopyright()->getHolder()) {
                $data['copyright']['holder'] = $object->getCopyright()->getHolder();
            }

            $data['authors'] = $object->getAuthors()->map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            })->toArray();

            if ($object->getIssue()) {
                $data['issue'] = $object->getIssue();
            }

            if ($object->getAbstract()) {
                $data['abstract'] = [
                    'content' => $object->getAbstract()->getContent()->map(function (Block $block) use (
                        $format,
                        $context
                    ) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray(),
                ];

                if ($object->getAbstract()->getDoi()) {
                    $data['abstract']['doi'] = $object->getAbstract()->getDoi();
                }
            }
        }

        return $this->normalizeArticle($object, $data, $format, $context);
    }

    abstract protected function denormalizeArticle($data, $class, $format = null, array $context = []) : ArticleVersion;

    abstract protected function normalizeArticle(
        ArticleVersion $article,
        array $data,
        $format = null,
        array $context = []
    ) : array;
}
