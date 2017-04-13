<?php
include __DIR__.'/../vendor/autoload.php';

// Guzzle logging.
$handler = GuzzleHttp\HandlerStack::create();
// Push the handler onto the handler stack
$handler->push(GuzzleHttp\Middleware::mapRequest(function (GuzzleHttp\Psr7\Request $request) {
    fwrite(STDERR, $request->getRequestTarget()."\n");
    // Notice that we have to return a request object
    return $request;
}));

$count = 0;
$handler->push(GuzzleHttp\Middleware::mapResponse(function (GuzzleHttp\Psr7\Response $response) use (&$count) {
    ++$count;
    fwrite(STDERR, "{$count} requests so far \n");
    // Notice that we have to return a request object
    return $response;
}));

$guzzle = new GuzzleHttp\Client([
    'handler' => $handler,
    'base_uri' => 'http://prod--gateway.elifesciences.org/',
    'headers' => [
    ],
]);

// Api SDK.
$client = new eLife\ApiClient\HttpClient\BatchingHttpClient(
    new eLife\ApiClient\HttpClient\Guzzle6HttpClient($guzzle),
    10
);
$sdk = new eLife\ApiSdk\ApiSdk($client);

