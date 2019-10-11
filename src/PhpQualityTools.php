<?php

namespace DanielWerner\PhpQualityTools;

use Composer\Json\JsonFormatter;

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
        $this->srcDirectory = $srcDirectory;
    }

    /**
     * @param string $destination
     */
    public function install($destination)
    {
        $this->copyStubs($destination);
        $this->setUpComposerJson($destination);
    }

    /**
     * @param $destination
     */
    protected function copyStubs(string $destination)
    {
        copy(__DIR__ . '/../phpmd.xml', $destination . '/phpmd.xml');
        copy(__DIR__ . '/../phpcs.xml', $destination . '/phpcs.xml');
    }

    protected function setUpComposerJson(string $destination)
    {
        $composerJson = $composerJson = $destination . '/composer.json';
        $composerSettings = $this->readComposerJson($composerJson);

        if (empty($composerSettings->scripts)) {
            $composerSettings['scripts'] = new \stdClass();
        }

        $composerSettings->scripts = (object) array_merge(
            (array) $composerSettings->scripts,
            (array) $this->getComposerScripts()
        );

        $this->writeComposerJson($composerJson, $composerSettings);
    }

    /**
     * @return array
     */
    protected function getComposerScripts(): array
    {
        return [
            "inspect" => [
                sprintf("vendor/bin/phpcs %s", $this->srcDirectory),
                sprintf("vendor/bin/phpstan analyze %s", $this->srcDirectory)
            ],
            "inspect-fix" => [
                sprintf("vendor/bin/php-cs-fixer fix %s", $this->srcDirectory),
                sprintf("vendor/bin/phpcbf", $this->srcDirectory)
            ],
            "insights" => sprintf("vendor/bin/phpmd %s text phpmd.xml", $this->srcDirectory)
        ];
    }

    /**
     * @param string $composerJson
     * @return object
     */
    protected function readComposerJson(string $composerJson): \stdClass
    {
        return json_decode(file_get_contents($composerJson));
    }

    /**
     * @param string $composerJson
     * @param array $composerSettings
     * @return bool|int
     */
    protected function writeComposerJson(string $composerJson, \stdClass $composerSettings)
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
}
