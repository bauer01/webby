<?php

namespace Webby\Console\Theme\Current;


use Nette\Utils\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webby\System\Theme;

class GetLayoutsCommand extends Command
{


    private $theme;

    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('current:getLayouts')
            ->setDescription('Get list of all available layouts from current theme.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (empty($this->theme->getCurrent())) {
            $output->getErrorOutput()->writeln("<error>No theme set</error>");
            return;
        }

        $result = [];
        if (is_dir($path = $this->theme->getDir() . "/layouts")) {
            foreach (Finder::findFiles('*.neon')->in($path) as $file) {
                $result[] = $file->getBasename('.neon');
            }
        }
        $output->writeln(json_encode($result));
    }

}