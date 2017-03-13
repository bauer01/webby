<?php

namespace Webby\Console;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\HttpAsset;
use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\LessphpFilter;
use Assetic\Filter\ScssphpFilter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System;
use Webby\Theme;

class Assets extends Command
{

    private $js;
    private $css;
    private $theme;

    public function __construct(AssetCollection $js, AssetCollection $css, System\Theme $theme)
    {
        $this->js = $js;
        $this->css = $css;
        $this->theme = $theme;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('assets:dump')
            ->setDescription('Generates assets.')
            ->setHelp('This command allows you to generate all frontend assets...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadThemeAssets("js");
        $this->loadThemeAssets("css");

        $assetManager = new AssetManager();
        $assetManager->set("js", $this->js);
        $assetManager->set("css", $this->css);

        $writer = new AssetWriter(WWW_DIR . "/" . $this->theme->getAssetsDir());
        $writer->writeManagerAssets($assetManager);
    }

    private function loadThemeAssets($name)
    {
        if (!empty($assets = $this->theme->getTheme()["assets"][$name])) {

            // CDN
            if (!empty($assets["cdn"])) {
                foreach ($assets["cdn"] as $url) {
                    $this->{$name}->add(new HttpAsset($url));
                }
            }

            // Local
            if (!empty($assets["local"])) {
                foreach ($assets["local"] as $path) {
                    $this->{$name}->add(new FileAsset($this->theme->getDir() . "/" . $path, $name === "styles" ? [
                        new ScssphpFilter(),
                        new LessphpFilter(),
                        new CssImportFilter()
                    ] : []));
                }
            }
        }
    }

}