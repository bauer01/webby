<?php

namespace Webby\Console\System\Particles;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System\Particles;

class GetAllCommand extends Command
{

    private $particles;

    public function __construct(Particles $particles)
    {
        $this->particles = $particles;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('particles:getAll')
            ->setDescription('Get all installed particles.')
            ->setHelp('This command allows you to get list of all installed particles..');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(json_encode($this->particles->getDefinitions()));
    }

}