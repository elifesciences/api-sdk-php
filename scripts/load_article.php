<?php
require_once __DIR__.'/bootstrap.php';

$articles = $sdk->articles();
$article = $articles->get($argv[1], $argv[2] ?: 1);
echo "{$argv[1]}v{$argv[2]} Authors: ".count($article->wait()->getAuthors()).PHP_EOL;
