<?php

namespace Webby\Console\System;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{

    protected function configure()
    {
        $this->setName('install')
            ->setDescription('Install app.')
            ->setHelp('This command allows you to install app...');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $assets = $this->getApplication()->find('assets:dump');
        $assets->run(new ArrayInput(['command' => 'assets:dump']), $output);

        $robots = $this->getApplication()->find('robots');
        $robots->run(new ArrayInput(['command' => 'robots']), $output);

        $sitemap = $this->getApplication()->find('sitemap');
        $sitemap->run(new ArrayInput(['command' => 'sitemap']), $output);
    }

}