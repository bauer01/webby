<?php

namespace Webby\Console\System;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System\Robots;

class RobotsCommand extends Command
{

    private $robots;

    public function __construct(Robots $robots)
    {
        $this->robots = $robots;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('robots')
            ->setDescription('Robots generator.')
            ->setHelp('This command allows you to generate robots.txt..');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->robots->isEnabled()) {

            $output->writeln("Dumping robots.txt...");
            $this->robots->dump();
        } else {
            $output->writeln("Robots disabled - skipped...");
        }
    }

}