<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class PressPackagesClient
{
    const TYPE_PRESS_PACKAGE = 'application/vnd.elife.press-package+json';
    const TYPE_PRESS_PACKAGE_LIST = 'application/vnd.elife.press-package-list+json';

    use ApiClient;

    public function getPackage(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "press-packages/$id"]), $headers);
    }

    public function listPackages(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        array $subjects = []
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'press-packages',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                    'subject[]' => $subjects,
                ],
            ]),
            $headers
        );
    }
}
