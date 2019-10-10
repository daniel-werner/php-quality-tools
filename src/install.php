<?php

$autoloaders = [__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php'];

foreach ($autoloaders as $autoloader) {
    if (is_file($autoloader)) {
        require_once $autoloader;
        break;
    }
}

$phpQualityTools = new DanielWerner\PhpQualityTools\PhpQualityTools();
$phpQualityTools->install(getcwd());