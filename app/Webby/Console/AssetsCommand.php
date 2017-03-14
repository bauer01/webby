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
use Webby\System\Assets;
use Webby\System\Theme;

class AssetsCommand extends Command
{

    private $assets;
    private $theme;
    private $js;
    private $css;

    public function __construct(Theme $theme, Assets $assets)
    {
        $this->theme = $theme;
        $this->assets = $assets;
        $this->js = $this->assets->getJs();
        $this->css = $this->assets->getCss();

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('assets:dump')
            ->setDescription('Dumps assets.')
            ->setHelp('This command allows you to generate all frontend assets...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Dumping CSS & JS files...");
        $this->dumpCssJs();

        $output->writeln("Dumping media files...");
        $this->dumpMedia();
    }

    private function dumpCssJs()
    {
        $this->loadThemeAssets("js");
        $this->loadThemeAssets("css");

        $assetManager = new AssetManager();
        $assetManager->set("js", $this->js);
        $assetManager->set("css", $this->css);

        $writer = new AssetWriter(WWW_DIR . "/assets");
        $writer->writeManagerAssets($assetManager);
    }

    private function dumpMedia()
    {
        if (!empty($media = $this->theme->getConfig()["assets"]["media"]["local"])) {

            mkdir($outputPath = WWW_DIR . "/assets/media/theme", 0775, true);
            foreach ($media as $relativePath) {
                shell_exec("cp -R " . $this->theme->getDir() . "/" . $relativePath . "/* " . $outputPath);
            }
        }
    }

    private function loadThemeAssets($type)
    {
        if (!empty($assets = $this->theme->getConfig()["assets"][$type])) {

            // CDN
            if (!empty($assets["cdn"])) {
                foreach ($assets["cdn"] as $url) {
                    $this->{$type}->add(new HttpAsset($url));
                }
            }

            // Local
            if (!empty($assets["local"])) {
                foreach ($assets["local"] as $path) {

                    $path = $this->theme->getDir() . "/" . $path;
                    $filters = [];

                    if ($type === "css") {

                        $filters[] = new CssImportFilter();
                        switch (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                            case "less":
                                $filters[] = new LessphpFilter();
                                break;
                            case "scss":
                                $filters[] = new ScssphpFilter();
                                break;
                        }
                    }

                    $this->{$type}->add(new FileAsset($path, $filters));
                }
            }
        }
    }

}