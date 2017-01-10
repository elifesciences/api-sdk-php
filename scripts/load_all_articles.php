<?php


include __DIR__.'/../vendor/autoload.php';

// Guzzle logging.
$handler = GuzzleHttp\HandlerStack::create();
// Push the handler onto the handler stack
$handler->push(GuzzleHttp\Middleware::mapRequest(function (GuzzleHttp\Psr7\Request $request) {
    echo $request->getRequestTarget()."\n";
    // Notice that we have to return a request object
    return $request;
}));

$count = 0;
$handler->push(GuzzleHttp\Middleware::mapResponse(function (GuzzleHttp\Psr7\Response $response) use (&$count) {
    ++$count;
    echo "{$count} requests so far \n";
    // Notice that we have to return a request object
    return $response;
}));

$guzzle = new GuzzleHttp\Client([
  'handler' => $handler,
  'base_uri' => 'http://prod--gateway.elifesciences.org/',
]);

// Api SDK.
$client = new eLife\ApiClient\HttpClient\BatchingHttpClient(
    new eLife\ApiClient\HttpClient\Guzzle6HttpClient($guzzle),
    100
);
$sdk = new eLife\ApiSdk\ApiSdk($client);

// TEST.
$articles = $sdk->articles();
$count = 0;
foreach ($articles as $a) {
    if ($a === null) {
        continue;
    }
    echo "Article id: {$a->getId()}", PHP_EOL;
    //$a->getCopyright();
    //echo "Article copyright loaded", PHP_EOL;
    ++$count;
    echo "Count: $count", PHP_EOL;
    echo 'Memory: ', memory_get_usage(true), ' bytes', PHP_EOL;
}
