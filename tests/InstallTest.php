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

        if (file_exists(__DIR__ . '/phpcs.xml')) {
            unlink(__DIR__ . '/phpcs.xml');
        }

        if (file_exists(__DIR__ . '/phpmd.xml')) {
            unlink(__DIR__ . '/phpmd.xml');
        }

        if (file_exists(__DIR__ . '/composer.json')) {
            unlink(__DIR__ . '/composer.json');
        }
    }

    /** @test */
    public function can_install_the_package()
    {
        $qualityTools = new PhpQualityTools('src');
        $qualityTools->install(__DIR__);

        $this->assertXmlFilesEquals();
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/expected/composer.json', __DIR__ . '/composer.json');
    }

    /** @test */
    public function can_install_the_package_with_install_php()
    {
        chdir(__DIR__);
        mkdir('src');
        require __DIR__ . '/../src/install.php';
        rmdir('src');

        $this->assertXmlFilesEquals();
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/expected/composer.json', __DIR__ . '/composer.json');
    }

    /** @test */
    public function can_install_the_package_without_script_in_composer_json()
    {
        unlink(__DIR__ . '/composer.json');
        copy(__DIR__ . '/resources/composer_no_script.json', __DIR__ . '/composer.json');

        $qualityTools = new PhpQualityTools('src');
        $qualityTools->install(__DIR__);

        $this->assertXmlFilesEquals();
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/expected/composer_no_script.json', __DIR__ . '/composer.json');
    }

    /** @test */
    public function can_guess_the_directory()
    {
        chdir(__DIR__);
        require_once __DIR__ . '/../src/helpers.php';

        mkdir('src');
        $this->assertEquals('src', guessSrcDirectory(__DIR__));
        rmdir('src');


        mkdir('app');
        $this->assertEquals('app', guessSrcDirectory(__DIR__));
        rmdir('app');

        $this->assertEquals('.', guessSrcDirectory(__DIR__));

    }

    /** @test */
    public function can_install_the_package_from_command_line()
    {
        chdir(__DIR__);

        mkdir('src');
        exec(__DIR__ . '/../bin/phpqt-install');
        rmdir('src');

        $this->assertXmlFilesEquals();
        $this->assertJsonFileEqualsJsonFile(__DIR__ . '/expected/composer.json', __DIR__ . '/composer.json');

    }

    /** @test */
    public function can_install_the_package_from_command_line_with_src_directory_argument()
    {
        chdir(__DIR__);
        exec(__DIR__ . '/../bin/phpqt-install ' . $this->srcDirectory);

        $this->assertXmlFilesEquals();

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

    protected function assertXmlFilesEquals(): void
    {
        $this->assertFileEquals(__DIR__ . '/expected/phpcs.xml', __DIR__ . '/phpcs.xml');
        $this->assertFileEquals(__DIR__ . '/expected/phpmd.xml', __DIR__ . '/phpmd.xml');
    }
}
