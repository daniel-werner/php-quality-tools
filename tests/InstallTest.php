<?php

namespace DanielWerner\PhpQualityTools\Tests;

use DanielWerner\PhpQualityTools\PhpQualityTools;
use PHPUnit\Framework\TestCase;

class InstallTest extends TestCase
{
    /**
     * @var string $srcDirectory
     */
    protected $srcDirectory = 'app';

    protected function setUp()
    {
        parent::setUp();
        copy(__DIR__ . '/resources/composer.json', __DIR__ . '/composer.json');
    }

    protected function tearDown()
    {
        parent::tearDown();

        unlink(__DIR__ . '/phpcs.xml');
        unlink(__DIR__ . '/phpmd.xml');
        unlink(__DIR__ . '/composer.json');
    }

    /** @test */
    public function can_install_the_package()
    {
        $qualityTools = new PhpQualityTools('src');
        $qualityTools->install(__DIR__);

        $this->assertFileExists(__DIR__ . '/phpcs.xml');
        $this->assertFileExists(__DIR__ . '/phpmd.xml');
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/../composer.json', __DIR__ . '/composer.json');
    }

    /** @test */
    public function can_install_the_package_from_command_line()
    {
        chdir(__DIR__);
        exec(__DIR__ . '/../bin/phpqt-install');

        $this->assertFileExists(__DIR__ . '/phpcs.xml');
        $this->assertFileExists(__DIR__ . '/phpmd.xml');
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/../composer.json', __DIR__ . '/composer.json');

    }

    /** @test */
    public function can_install_the_package_from_command_line_with_src_directory_argument()
    {
        chdir(__DIR__);
        exec(__DIR__ . '/../bin/phpqt-install ' . $this->srcDirectory);

        $this->assertFileExists(__DIR__ . '/phpcs.xml');
        $this->assertFileExists(__DIR__ . '/phpmd.xml');

        $jsonSettings = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

        $this->assertEquals($jsonSettings['scripts']['inspect'],
            [
                sprintf("vendor/bin/phpcs %s", $this->srcDirectory),
                sprintf("vendor/bin/phpstan analyze %s", $this->srcDirectory)
            ]
        );

        $this->assertEquals($jsonSettings['scripts']['inspect-fix'],
            [
                sprintf("vendor/bin/php-cs-fixer fix %s", $this->srcDirectory),
                sprintf("vendor/bin/phpcbf %s", $this->srcDirectory)
            ]
        );

        $this->assertEquals($jsonSettings['scripts']['insights'],
                sprintf("vendor/bin/phpmd %s text phpmd.xml", $this->srcDirectory)
        );

    }
}
