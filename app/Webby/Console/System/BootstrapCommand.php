<?php

namespace Webby\Console\System;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BootstrapCommand extends Command
{

    private $contentPath;
    private $bootstrapPath;

    public function __construct($bootstrapPath, $contentPath)
    {
        $this->bootstrapPath = $bootstrapPath;
        $this->contentPath = $contentPath;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('bootstrap')
            ->setDescription('Bootstrap app.')
            ->setHelp('Creates new empty structure...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Cleaning content...");
        if ($err = shell_exec("rm -rf " . $this->contentPath . "/* 2>&1 1> /dev/null")) {
            $output->getErrorOutput()->writeln("<error>$err</error>");
            return;
        }

        $output->writeln("Copying bootstrap content...");
        if ($err = shell_exec("cp -r " . $this->bootstrapPath . "/* " . $this->contentPath)) {
            $output->getErrorOutput()->writeln("<error>$err</error>");
            return;
        }
    }

}