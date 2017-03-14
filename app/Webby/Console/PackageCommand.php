<?php

namespace Webby\Console;

use Composer\Command\RequireCommand;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PackageCommand extends Command
{


    protected function configure()
    {
        $this->setName('package')
            ->setDescription('Package manager.')
            ->setHelp('This command allows you to manage packages..');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @link https://gist.github.com/dmouse/7532532
         */
        $input->
        $helper = new HelperSet();
        $io = new ConsoleIO($input, $output, $helper);
        $composer = Factory::create($io);
        $composerRequire = new RequireCommand();
        $composerRequire->setComposer($composer);
        $composerRequire->run($input, $output);

        $output->writeln("Installing packages");
//        $output->writeln(
//            system($this->composer . ' --working-dir="' . __DIR__ . '/../" --no-interaction --no-dev --prefer-dist -o install')
//        );
    }

    public static function getComposerLocation()
    {
        if (!function_exists('shell_exec') || strtolower(substr(PHP_OS, 0, 3)) === 'win') {
            throw new \Exception("Missing shell_exec() or Windows!");
        }

        // check for global composer install
        $path = trim(shell_exec("command -v composer"));
        // fall back to grav bundled composer
        if (!$path || !preg_match('/(composer|composer\.phar)$/', $path)) {
            throw new \Exception("Composer not installed!");
        }
        return $path;
    }

    public static function getComposerExecutor()
    {
        $executor = PHP_BINARY . ' ';
        $composer = static::getComposerLocation();
        if ($composer !== static::DEFAULT_PATH && is_executable($composer)) {
            $file = fopen($composer, 'r');
            $firstLine = fgets($file);
            fclose($file);
            if (!preg_match('/^#!.+php/i', $firstLine)) {
                $executor = '';
            }
        }
        return $executor . $composer;
    }

}