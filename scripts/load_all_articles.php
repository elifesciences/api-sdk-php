<?php

require_once __DIR__.'/bootstrap.php';

// TEST.
$articles = $sdk->articles();
$articlesCount = 0;
$invalidArticles = 0;
$articleIds = [];
echo 'ARTICLE IDS', PHP_EOL;
foreach ($articles as $a) {
    if (null === $a) {
        ++$invalidArticles;
        continue;
    }
    echo "Article id: {$a->getId()}", PHP_EOL;
    //$a->getCopyright();
    //echo 'Article copyright loaded', PHP_EOL;
    ++$articlesCount;
    $articleIds[] = $a->getId();
    //echo "Count: $articlesCount", PHP_EOL;
    //echo 'Memory: ', memory_get_usage(true), ' bytes', PHP_EOL;
}
echo 'ARTICLE VERSION NUMBERS', PHP_EOL;
$versionsByArticle = [];
$versionsCount = 0;
$histories = [];
foreach ($articleIds as $id) {
    $histories[$id] = $articles->getHistory($id);
}

echo 'ARTICLE VERSIONS', PHP_EOL;
foreach ($histories as $id => $history) {
    foreach ($history->wait()->getVersions() as $article) {
        $versionNumber = $article->getVersion();
        $versionsByArticle[$id][$versionNumber] = $articles->get($id, $versionNumber);
        ++$versionsCount;
    }
}

$totalVersions = 0;
foreach ($versionsByArticle as $id => $versions) {
    foreach ($versions as $versionNumber => $version) {
        try {
            $article = $version->wait();
            echo "Authors ({$id}v{$versionNumber}): ", count($article->getAuthors()), PHP_EOL;
            ++$totalVersions;
        } catch (RuntimeException $e) {
            echo "Failure in Authors ({$id}v{$versionNumber}): ", $e->getMessage(), PHP_EOL;
            throw $e;
        }
    }
}
echo "Invalid articles (not served): $invalidArticles", PHP_EOL;
echo "Valid articles (served): $articlesCount", PHP_EOL;
echo "Valid versions (served): $versionsCount", PHP_EOL;
echo '$articleIds: ', count($articleIds), PHP_EOL;
echo '$versions (indexed by article): ', count($versionsByArticle), PHP_EOL;
echo "Total versions (served): $totalVersions", PHP_EOL;
