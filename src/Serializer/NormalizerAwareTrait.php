<?php

namespace eLife\ApiSdk\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @deprecated Will be replaced by Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait
 *             when symfony/serializer dependency is bumped to ^3.1
 */
trait NormalizerAwareTrait
{
    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }
}
