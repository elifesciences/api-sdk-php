<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Model\ArticleVoR;
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
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class ArticleVoRNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use SubjectsAware;

    public function denormalize($data, $class, $format = null, array $context = []) : ArticleVoR
    {
        if (empty($data['abstract'])) {
            $data['abstract'] = promise_for(null);
        } else {
            $data['abstract'] = promise_for($data['abstract'])
                ->then(function ($abstract) use ($format, $context) {
                    if (empty($abstract)) {
                        return null;
                    }

                    return new ArticleSection(
                        new ArrayCollection(array_map(function (array $block) use ($format, $context) {
                            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                        }, $abstract['content'])),
                        $abstract['doi'] ?? null
                    );
                });
        }

        $data['authors'] = new PromiseCollection(promise_for($data['authors'])
            ->then(function (array $authors) use ($format, $context) {
                return array_map(function (array $author) use ($format, $context) {
                    return $this->denormalizer->denormalize($author, AuthorEntry::class, $format, $context);
                }, $authors);
            }));

        $data['body'] = new PromiseCollection(promise_for($data['body'])
            ->then(function (array $blocks) use ($format, $context) {
                return array_map(function (array $block) use ($format, $context) {
                    return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                }, $blocks);
            }));

        $data['copyright'] = promise_for($data['copyright'])
            ->then(function (array $copyright) {
                return new Copyright($copyright['license'], $copyright['statement'], $copyright['holder'] ?? null);
            });

        if (empty($data['digest'])) {
            $data['digest'] = promise_for(null);
        } else {
            $data['digest'] = promise_for($data['digest'])
                ->then(function ($digest) use ($format, $context) {
                    if (empty($digest)) {
                        return null;
                    }

                    return new ArticleSection(
                        new ArrayCollection(array_map(function (array $block) use ($format, $context) {
                            return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                        }, $digest['content'])),
                        $digest['doi']
                    );
                });
        }

        $data['issue'] = promise_for($data['issue'] ?? null);

        $data['keywords'] = new PromiseCollection(promise_for($data['keywords'] ?? []));

        $data['subjects'] = !empty($data['subjects']) ? $this->getSubjects($data['subjects']) : null;

        return new ArticleVoR(
            $data['id'],
            $data['version'],
            $data['type'],
            $data['doi'],
            $data['authorLine'],
            $data['title'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']),
            $data['volume'],
            $data['elocationId'],
            $data['pdf'] ?? null,
            $data['subjects'],
            $data['researchOrganisms'] ?? [],
            $data['abstract'],
            $data['issue'],
            $data['copyright'],
            $data['authors'],
            $data['impactStatement'],
            $data['keywords'],
            $data['digest'],
            $data['body']
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return
            ArticleVoR::class === $type
            ||
            (ArticleVersion::class === $type && 'vor' === $data['status']);
    }

    /**
     * @param ArticleVoR $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'version' => $object->getVersion(),
            'type' => $object->getType(),
            'doi' => $object->getDoi(),
            'authorLine' => $object->getAuthorLine(),
            'title' => $object->getTitle(),
            'published' => $object->getPublishedDate()->format(DATE_ATOM),
            'volume' => $object->getVolume(),
            'elocationId' => $object->getElocationId(),
        ];

        if ($object->getPdf()) {
            $data['pdf'] = $object->getPdf();
        }

        if ($object->hasSubjects()) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) {
                return $subject->getId();
            });
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
            });

            if ($object->getAbstract()) {
                $data['abstract'] = [
                    'content' => $object->getAbstract()->getContent()->map(function (Block $block) use (
                        $format,
                        $context
                    ) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray(),
                    'doi' => $object->getAbstract()->getDoi(),
                ];
            }

            if ($object->getIssue()) {
                $data['issue'] = $object->getIssue();
            }

            if (count($object->getKeywords())) {
                $data['keywords'] = $object->getKeywords();
            }

            if ($object->getDigest()) {
                $data['digest'] = [
                    'content' => $object->getAbstract()->getContent()->map(function (Block $block) use (
                        $format,
                        $context
                    ) {
                        return $this->normalizer->normalize($block, $format, $context);
                    })->toArray(),
                    'doi' => $object->getAbstract()->getDoi(),
                ];
            }

            $data['body'] = $object->getContent()->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            });
        }

        return all($data)->wait();
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ArticleVoR;
    }
}
