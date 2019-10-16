<?php

$cwd = getcwd();

$autoloaders = [__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php'];

foreach ($autoloaders as $autoloader) {
    if (is_file($autoloader)) {
        require_once $autoloader;
        break;
    }
}

$srcDirectory = $argv[1] ?? guessSrcDirectory($cwd);
$phpQualityTools = new DanielWerner\PhpQualityTools\PhpQualityTools($srcDirectory);

try {
    $phpQualityTools->install($cwd);
} catch (Exception $ex) {
    echo $ex->getMessage();
}
