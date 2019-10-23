<?php

namespace DanielWerner\PhpQualityTools;

use \stdClass;
use \Exception;

class PhpQualityTools
{
    /**
     * @var string $srcDirectory
     */
    protected $srcDirectory;

    /**
     * PhpQualityTools constructor.
     * @param string $srcDirectory
     */
    public function __construct(string $srcDirectory)
    {
        echo sprintf('Using source directory: "%s"', $srcDirectory) . PHP_EOL;
        $this->srcDirectory = $srcDirectory;
    }

    /**
     * @param $destination
     * @throws Exception
     */
    public function install($destination)
    {
        $this->copyStubs($destination);
        $this->setUpComposerJson($destination);

        echo 'Done.' . PHP_EOL;
    }

    /**
     * @param string $destination
     * @throws Exception
     */
    protected function copyStubs(string $destination)
    {
        echo 'Copying configuration files.' . PHP_EOL;
        $this->createDestinationIfNotExist($destination);
        $this->copyFile('phpmd.xml', $destination);
        $this->copyFile('phpcs.xml', $destination);
    }

    /**
     * @param string $destination
     * @throws Exception
     */
    protected function setUpComposerJson(string $destination)
    {
        echo 'Setting up composer.json scripts.' . PHP_EOL;

        $composerJson = $destination . '/composer.json';
        if (!file_exists($composerJson)) {
            throw new Exception('File composer.json is missed! Please ensure that you are in root folder.');
        }
        $composerSettings = $this->readComposerJson($composerJson);
        
        if (is_null($composerSettings)) {
            throw new Exception('File composer.json is corrupted!');
        }
        
        if (empty($composerSettings->scripts)) {
            $composerSettings->scripts = new stdClass();
        }

        $composerSettings->scripts = (object) array_merge(
            (array) $composerSettings->scripts,
            (array) $this->getComposerScripts()
        );

        if (!$this->writeComposerJson($composerJson, $composerSettings)) {
            throw new Exception('Cannot write new composer.json!');
        }
    }

    /**
     * @return array
     */
    protected function getComposerScripts(): array
    {
        return [
            "inspect" => [
                sprintf("phpcs %s", $this->srcDirectory),
                sprintf("phpstan analyze %s", $this->srcDirectory)
            ],
            "inspect-fix" => [
                sprintf("php-cs-fixer fix %s", $this->srcDirectory),
                sprintf("phpcbf %s", $this->srcDirectory)
            ],
            "insights" => sprintf("phpmd %s text phpmd.xml", $this->srcDirectory)
        ];
    }

    /**
     * @param string $composerJson
     * @return stdClass
     * @throws Exception
     */
    protected function readComposerJson(string $composerJson): \stdClass
    {
        $content = file_get_contents($composerJson);
        if (!$content) {
            throw new Exception('File composer.json is empty!');
        }
        return json_decode($content);
    }

    /**
     * @param string $composerJson
     * @param stdClass $composerSettings
     * @return bool|int
     */
    protected function writeComposerJson(string $composerJson, stdClass $composerSettings)
    {
        return file_put_contents(
            $composerJson,
            json_encode(
                $composerSettings,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_LINE_TERMINATORS |
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
            )
        );
    }

    private function createDestinationIfNotExist($destination)
    {
        if (is_null($destination)) {
            throw new Exception('Failed to create required folders!');
        }
        if (!file_exists($destination)) {
            if (!mkdir($destination, 0777, true)) {
                throw new Exception('Failed to create required folders! Please check your write permission.');
            }
        }
    }

    private function copyFile($filename, $destination)
    {
        if (!file_exists(__DIR__ . "/../$filename") || !copy(__DIR__ . "/../$filename", $destination . "/$filename")) {
            throw new Exception(sprintf("File %s cannot be created! Please check your write permission.", $filename));
        }
    }
}
