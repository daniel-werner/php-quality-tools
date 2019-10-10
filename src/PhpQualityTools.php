<?php

namespace DanielWerner\PhpQualityTools;

class PhpQualityTools
{
    public function install($destination)
    {
        $this->copyStubs($destination);
        $this->setUpComposerJson($destination);
    }

    /**
     * @param $destination
     */
    protected function copyStubs($destination)
    {
        copy(__DIR__ . '/../phpmd.xml', $destination . '/phpmd.xml');
        copy(__DIR__ . '/../phpcs.xml', $destination . '/phpcs.xml');
    }

    protected function setUpComposerJson($destination)
    {
        $composerJson = $destination . '/composer.json';
        $composerSettings = json_decode(file_get_contents($composerJson), true);

        if (empty($composerSettings['scripts'])) {
            $composerSettings['scripts'] = [];
        }

        $composerSettings['scripts'] = array_merge(
            $composerSettings['scripts'],
            [
                "inspect" => [
                    "vendor/bin/phpcs",
                    "vendor/bin/phpstan analyze src"
                ],
                "inspect-fix" => [
                    "vendor/bin/php-cs-fixer fix src",
                    "vendor/bin/phpcbf"
                ],
                "insights" => "vendor/bin/phpmd src text phpmd.xml"
            ]
        );

        file_put_contents(
            $composerJson,
            json_encode(
                $composerSettings,
                JSON_PRETTY_PRINT +
                JSON_UNESCAPED_LINE_TERMINATORS +
                JSON_UNESCAPED_SLASHES +
                JSON_UNESCAPED_UNICODE
            )
        );
    }
}
