<?php

namespace Webby\Console\Theme\Current;


use Nette\Neon\Neon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System\Theme;

class DeleteLayoutCommand extends Command
{


    private $theme;

    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName("current:deleteLayout")
            ->addArgument("name", InputArgument::REQUIRED, "Layout name.")
            ->setDescription("Delete layout in current theme.");
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
        unlink($path);
    }

}