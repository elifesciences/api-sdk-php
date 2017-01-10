<?php


include __DIR__.'/../vendor/autoload.php';

$handler = GuzzleHttp\HandlerStack::create();
$count = 0;
$handler->push(GuzzleHttp\Middleware::mapRequest(function (GuzzleHttp\Psr7\Request $request) use (&$count) {
    ++$count;
    echo $request->getRequestTarget()."\n";

    return $request;
}));

$guzzle = new GuzzleHttp\Client([
  'handler' => $handler,
  'base_uri' => 'http://prod--gateway.elifesciences.org/',
]);

$client = new eLife\ApiClient\HttpClient\BatchingHttpClient(
    new eLife\ApiClient\HttpClient\Guzzle6HttpClient($guzzle),
    100
);
$sdk = new eLife\ApiSdk\ApiSdk($client);

// TEST.
$articles = $sdk->articles();
$total = $articles->count();
$allArticlesObjectsHashesEverSeen = [];
$allArticlesObjectsEverSeen = [];
for ($i = 0; $i < 100; ++$i) {
    $articleObjectsHashes = [];
    $articleObjects = [];
    $offset = rand(0, $total);
    $limit = 100;
    echo "Slicing $offset, $limit", PHP_EOL;
    $countBefore = $count;
    foreach ($articles->slice($offset, $limit) as $a) {
        if ($a === null) {
            continue;
        }
        // uncomment to force full loading
        //$a->getCopyright();
        $articleObjectsHashes[] = $hash = spl_object_hash($a);
        $articleObjects[$hash] = $a;
        $allArticlesObjectsEverSeen[$hash] = $a;
        //echo $hash, " ", $a->getTitle(), PHP_EOL;
    }
    if ($intersection = array_intersect($articleObjectsHashes, $allArticlesObjectsHashesEverSeen)) {
        echo 'Seeing again old objects...', PHP_EOL;
        var_dump($intersection);
        foreach ($intersection as $hash) {
            var_dump($articleObjects[$hash]->getTitle());
        }
        throw new RuntimeException('We should only receive new objects');
    }
    $allArticlesObjectsHashesEverSeen = array_merge($allArticlesObjectsHashesEverSeen, $articleObjectsHashes);
    echo 'Seen ', count($allArticlesObjectsHashesEverSeen), ' objects so far', PHP_EOL;
}
