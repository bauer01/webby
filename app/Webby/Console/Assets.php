<?php

namespace Webby\Console;

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

class Assets extends Command
{

    private $system;

    public function __construct(System $system)
    {
        $this->system = $system;
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
        $this->loadThemeAssets(System::ASSETS_SCRIPTS);
        $this->loadThemeAssets(System::ASSETS_STYLES);

        $assetManager = new AssetManager();
        $assetManager->set(System::ASSETS_SCRIPTS, $this->system->getAsset(System::ASSETS_SCRIPTS));
        $assetManager->set(System::ASSETS_STYLES,  $this->system->getAsset(System::ASSETS_STYLES));

        $writer = new AssetWriter(WWW_DIR . "/assets");
        $writer->writeManagerAssets($assetManager);
    }

    private function loadThemeAssets($name)
    {
        if (!empty($assets = $this->system->getTheme()["assets"][$name])) {

            $asset = $this->system->getAsset($name);

            // CDN
            if (!empty($assets["cdn"])) {
                foreach ($assets["cdn"] as $url) {
                    $asset->add(new HttpAsset($url));
                }
            }

            // Local
            if (!empty($assets["local"])) {
                foreach ($assets["local"] as $path) {
                    $asset->add(new FileAsset($this->system->getThemeDir() . "/" . $path, $name === "styles" ? [
                        new ScssphpFilter(),
                        new LessphpFilter(),
                        new CssImportFilter()
                    ] : []));
                }
            }
        }
    }

}