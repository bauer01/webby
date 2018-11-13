<?php

namespace Webby\Console;

use Assetic\Asset\FileAsset;
use Assetic\Asset\HttpAsset;
use Assetic\AssetManager;
use Assetic\AssetWriter;
use Assetic\Filter\CssImportFilter;
use Assetic\Filter\CssMinFilter;
use Assetic\Filter\JSMinFilter;
use Assetic\Filter\LessFilter;
use Assetic\Filter\ScssphpFilter;
use Nette\InvalidArgumentException;
use Nette\Utils\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System\Assets;
use Webby\System\Theme;

class_alias("JSMin\JSMin", "JSMin");

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
        if (empty($this->theme->getCurrent())) {
            $output->writeln("No theme set");
            return;
        }

        $output->writeln("Cleaning assets dir...");
        shell_exec("rm -rf " . WWW_DIR . "/assets");

        $output->writeln("Dumping CSS & JS files...");
        $this->dumpCssJs();

        $output->writeln("Dumping media files...");
        $this->dumpMedia();

        $output->writeln("Dumping favicon...");
        $this->dumpFavicon();
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

    private function dumpFavicon()
    {
        if (!empty($this->theme->getConfig()["favicon"])) {

            if (!is_dir($outputPath = WWW_DIR . "/assets/theme/favicon")) {
                mkdir($outputPath, 0775, true);
            }

            $ico_lib = new \PHP_ICO(
                $this->theme->getDir() . "/" . $this->theme->getConfig()["favicon"],
                [
                    [16, 16]
                ]
            );
            $ico_lib->save_ico($outputPath . "/favicon.ico");
        }
    }

    private function dumpMedia()
    {
        if (!empty($media = $this->theme->getConfig()["media"])) {

            if (!is_dir($outputPath = WWW_DIR . "/assets/theme/media")) {
                mkdir($outputPath, 0775, true);
            }

            foreach ($media as $relativePath) {

                if (!is_dir($sourcePath = $this->theme->getDir() . "/" . $relativePath)) {
                    throw new InvalidArgumentException("Can not dump missing dir " . $sourcePath);
                }

                foreach (Finder::findFiles("*")->in($sourcePath) as $file) {
                    copy($file->getPathname(), $outputPath . "/" . $file->getFilename());
                }
            }
        }
    }

    private function loadThemeAssets($type)
    {
        if (!empty($assets = $this->theme->getConfig()[$type])) {

            // CDN
            if (!empty($assets["cdn"])) {
                foreach (array_reverse($assets["cdn"]) as $url) {

                    $filters = [];
                    if ($type === "css") {
                        $filters[] = new CssMinFilter();
                    } else if ($type === "js") {
                        $filters[] = new JSMinFilter();
                    }

                    $this->{$type}->add(new HttpAsset($url, $filters));
                }
            }

            // Local
            if (!empty($assets["local"])) {
                foreach (array_reverse($assets["local"]) as $path) {

                    $filePath = $this->theme->getDir() . "/" . $path;
                    if (!is_file($filePath)) {
                        // Try to load parent
                        $filePath = $this->theme->getDir() . "/../" . $this->theme->getParent() . "/" . $path;
                    }

                    $filters = [];

                    if ($type === "css") {

                        $filters[] = new CssImportFilter();
                        switch (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                            case "less":
                                $filters[] = new LessFilter("/usr/bin/node", ["/usr/lib/node_modules"]);
                                break;
                            case "scss":
                                $filters[] = new ScssphpFilter();
                                break;
                        }
                        $filters[] = new CssMinFilter();
                    } else if ($type === "js") {
                        $filters[] = new JSMinFilter();
                    }

                    $this->{$type}->add(new FileAsset($filePath, $filters));
                }
            }
        }
    }

}