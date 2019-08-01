<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\BioprotocolClient;
use eLife\ApiClient\MediaType;
use eLife\ApiSdk\Model\Bioprotocol;
use eLife\ApiSdk\Model\Identifier;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Bioprotocols
{
    const VERSION_BIOPROTOCOL = 1;

    private $bioprotocolClient;
    private $denormalizer;

    public function __construct(BioprotocolClient $bioprotocolClient, DenormalizerInterface $denormalizer)
    {
        $this->bioprotocolClient = $bioprotocolClient;
        $this->denormalizer = $denormalizer;
    }

    public function list(Identifier $identifier) : PromiseInterface
    {
        return $this->bioprotocolClient
            ->list(
                [
                    'Accept' => (string) new MediaType(BioprotocolClient::TYPE_BIOPROTOCOL, self::VERSION_BIOPROTOCOL),
                ],
                $identifier->getType(),
                $identifier->getId()
            )
            ->then(
                function (array $result) : array {
                    return array_reduce(
                        $result['items'],
                        function (array $carry, array $bioprotocolData) : array {
                            /** @var Bioprotocol $bioprotocol */
                            $bioprotocol = $this->denormalizer->denormalize($bioprotocolData, Bioprotocol::class);

                            $carry[$bioprotocol->getSectionid()] = $bioprotocol;

                            return $carry;
                        },
                        []
                    );
                }
            );
    }
}
