<?php

namespace test\eLife\ApiSdk;

use eLife\ApiValidator\Exception\InvalidMessage;
use eLife\ApiValidator\MessageValidator;
use Kevinrob\GuzzleCache\CacheEntry;
use Kevinrob\GuzzleCache\Strategy\CacheStrategyInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class ValidatingStorageAdapter implements CacheStrategyInterface
{
    private CacheStrategyInterface $storageAdapter;
    private MessageValidator $validator;

    public function __construct(CacheStrategyInterface $storageAdapter, MessageValidator $validator)
    {
        $this->storageAdapter = $storageAdapter;
        $this->validator = $validator;
    }

    public function fetch(RequestInterface $request): ?CacheEntry
    {
        return $this->storageAdapter->fetch($request);
    }

    public function save(RequestInterface $request, ResponseInterface $response): void
    {
        try {
            $this->validator->validate($request);
        } catch (InvalidMessage $e) {
            throw new RuntimeException('Request JSON schema validation failed: '.$this->dumpJsonBody($request), -1, $e);
        }
        try {
            $this->validator->validate($response);
        } catch (InvalidMessage $e) {
            throw new RuntimeException('Response JSON schema validation failed: '.$this->dumpJsonBody($response), -1, $e);
        }

        $this->storageAdapter->save($request, $response);
    }

    public function cache(RequestInterface $request, ResponseInterface $response): bool
    {
        return $this->storageAdapter->cache($request, $response);
    }

    public function update(RequestInterface $request, ResponseInterface $response): bool
    {
        return $this->storageAdapter->update($request, $response);
    }

    public function delete(RequestInterface $request): bool
    {
        return $this->storageAdapter->delete($request);
    }

    private function dumpJsonBody(MessageInterface $message): string
    {
        return json_encode(
            json_decode((string) $message->getBody(), true),
            JSON_PRETTY_PRINT
        );
    }
}
