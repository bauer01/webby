<?php

namespace Webby\Console\System;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System\Sitemap;

class SitemapCommand extends Command
{

    private $sitemap;

    public function __construct(Sitemap $sitemap)
    {
        $this->sitemap = $sitemap;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('sitemap')
            ->setDescription('Sitemap generator.')
            ->setHelp('This command allows you to generate sitemap..');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->sitemap->isEnabled()) {

            $output->writeln("Dumping sitemap...");
            $this->sitemap->dump();
        } else {
            $output->writeln("Sitemap disabled - skipped...");
        }
    }

}