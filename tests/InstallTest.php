<?php

namespace DanielWerner\PhpQualityTools\Tests;

use DanielWerner\PhpQualityTools\PhpQualityTools;
use PHPUnit\Framework\TestCase;

class InstallTest extends TestCase
{
    /** @test */
    public function can_install_the_package()
    {
        copy(__DIR__ . '/resources/composer.json', __DIR__ . '/composer.json');

        $qualityTools = new PhpQualityTools();
        $qualityTools->install(__DIR__);

        $this->assertFileExists(__DIR__ . '/phpcs.xml');
        $this->assertFileExists(__DIR__ . '/phpmd.xml');

        unlink(__DIR__ . '/phpcs.xml');
        unlink(__DIR__ . '/phpmd.xml');

        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/../composer.json', __DIR__ . '/composer.json');

        unlink(__DIR__ . '/composer.json');
    }
}
