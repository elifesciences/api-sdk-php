<?php

namespace test\eLife\ApiSdk;

use DateTime;
use Kevinrob\GuzzleCache\CacheEntry;
use Kevinrob\GuzzleCache\Strategy\CacheStrategyInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class InMemoryStorageAdapter implements CacheStrategyInterface
{
    private array $array = [];
    private array $requestHeadersBlacklist;

    public function __construct(array $requestHeadersBlacklist = [])
    {
        $this->requestHeadersBlacklist = !empty($requestHeadersBlacklist)
            ? $requestHeadersBlacklist
            : ['User-Agent', 'Host'];
    }

    public function fetch(RequestInterface $request): ?CacheEntry
    {
        return $this->array[$this->getKey($request)] ?? null;
    }

    public function save(RequestInterface $request, ResponseInterface $response): void
    {
        $this->array[$this->getKey($request)] = new CacheEntry($request, $response, new DateTime('+1 year'));
    }

    public function cache(RequestInterface $request, ResponseInterface $response): bool
    {
        return false;
    }

    public function update(RequestInterface $request, ResponseInterface $response): bool
    {
        return false;
    }

    public function delete(RequestInterface $request): bool
    {
        unset($this->array[$this->getKey($request)]);
        return true;
    }

    private function getKey(RequestInterface $request): string
    {
        return md5(serialize([
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'query' => $request->getUri()->getQuery(),
            'user_info' => $request->getUri()->getUserInfo(),
            'port' => $request->getUri()->getPort(),
            'scheme' => $request->getUri()->getScheme(),
            'headers' => array_diff_key($request->getHeaders(), array_flip($this->requestHeadersBlacklist)),
        ]));
    }
}
