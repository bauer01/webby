<?php

namespace Webby\Console\Theme\Current;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System\Theme;

class GetParticlesCommand extends Command
{


    private $theme;

    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('current:getParticles')
            ->setDescription('Get list of all available particles from current theme.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (empty($this->theme->getCurrent())) {
            $output->getErrorOutput()->writeln("<error>No theme set</error>");
            return;
        }

        $config = $this->theme->getConfig();
        $output->writeln(json_encode(empty($config["particles"]) ? [] : $config["particles"]));
    }

}