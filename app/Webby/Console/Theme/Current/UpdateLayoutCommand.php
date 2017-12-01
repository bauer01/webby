<?php

namespace Webby\Console\Theme\Current;


use Nette\Neon\Neon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System\Theme;

class UpdateLayoutCommand extends Command
{


    private $theme;

    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName("current:updateLayout")
            ->addArgument("name", InputArgument::REQUIRED, "Layout name.")
            ->addArgument("content", InputArgument::REQUIRED, "Layout content in JSON format.")
            ->setDescription("Update layout in current theme.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (empty($this->theme->getCurrent())) {
            $output->getErrorOutput()->writeln("<error>No theme set</error>");
            return;
        }

        $fileName = str_replace('/', '', $input->getArgument("name"));
        $path = $this->theme->getDir() . "/layouts/" . $fileName . ".neon";
        if (!is_file($path)) {
            $output->getErrorOutput()->writeln("<error>Layout " . $fileName . " not found!</error>");
            return;
        }

        if ($content = $input->getArgument("content")) {
            $content = json_decode($content);
            if ($jsonError = json_last_error() !== JSON_ERROR_NONE) {
                $output->getErrorOutput()->writeln("<error>JSON content parse: " . json_last_error_msg() . "</error>");
                return;
            }
            $content = Neon::encode($content, Neon::BLOCK);
        }
        file_put_contents($path, $content);
    }

}