<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiClient\ProfilesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Client\Profiles;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Profile;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ProfileNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(ProfilesClient $profilesClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $profile) : string {
                return $profile['id'];
            },
            function (string $id) use ($profilesClient) : PromiseInterface {
                return $profilesClient->getProfile(
                    ['Accept' => (string) new MediaType(ProfilesClient::TYPE_PROFILE, Profiles::VERSION_PROFILE)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []) : Profile
    {
        if (!empty($context['snippet'])) {
            $profile = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['affiliations'] = new PromiseSequence($profile
                ->then(function (Result $profile) {
                    return $profile['affiliations'] ?? [];
                }));

            $data['emailAddresses'] = new PromiseSequence($profile
                ->then(function (Result $profile) {
                    return $profile['emailAddresses'] ?? [];
                }));
        } else {
            $data['affiliations'] = new ArraySequence($data['affiliations'] ?? []);

            $data['emailAddresses'] = new ArraySequence($data['emailAddresses'] ?? []);
        }

        $data['affiliations'] = $data['affiliations']->map(function (array $accessControl) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($accessControl, AccessControl::class, $format, $context + ['class' => Place::class]);
        });

        $data['emailAddresses'] = $data['emailAddresses']->map(function (array $accessControl) use ($format, $context) {
            unset($context['snippet']);

            return $this->denormalizer->denormalize($accessControl, AccessControl::class, $format, $context);
        });

        return new Profile(
            $data['id'],
            $this->denormalizer->denormalize($data, PersonDetails::class, $format, $context),
            $data['affiliations'],
            $data['emailAddresses']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return Profile::class === $type;
    }

    /**
     * @param Profile $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = $this->normalizer->normalize($data->getDetails(), $format, $context);

        $arr['id'] = $data->getId();

        if (empty($context['snippet'])) {
            if ($data->getAffiliations()->notEmpty()) {
                $arr['affiliations'] = $data->getAffiliations()->map(function (AccessControl $accessControl) use ($format, $context) {
                    return $this->normalizer->normalize($accessControl, $format, $context);
                })->toArray();
            }
            if ($data->getEmailAddresses()->notEmpty()) {
                $arr['emailAddresses'] = $data->getEmailAddresses()->map(function (AccessControl $accessControl) use ($format, $context) {
                    return $this->normalizer->normalize($accessControl, $format, $context);
                })->toArray();
            }
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Profile;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Profile::class => false,
        ];
    }
}
