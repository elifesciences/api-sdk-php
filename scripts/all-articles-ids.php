<?php
require_once __DIR__.'/bootstrap.php';

// TEST.
$articles = $sdk->articles();
$articlesCount = 0;
$invalidArticles = 0;
$articleIds = [];
foreach ($articles as $a) {
    if ($a === null) {
        ++$invalidArticles;
        continue;
    }
    $articleIds[] = $a->getId();
}
$versionsByArticle = [];
$versionsCount = 0;
$histories = [];
foreach ($articleIds as $id) {
    $histories[$id] = $articles->getHistory($id);
}

foreach ($histories as $id => $history) {
    foreach ($history->wait()->getVersions() as $article) {
        $versionNumber = $article->getVersion();
        echo "$id,$versionNumber", PHP_EOL;
    }
}

