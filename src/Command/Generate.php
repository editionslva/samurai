<?php
namespace Samurai\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommandLine
 * @package Samurai\Composer
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class Generate extends Command
{

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Generates a new project')
            ->addArgument(
                'bootstrap',
                InputArgument::OPTIONAL,
                'package name',
                'raphhh/php-lib-bootstrap'
            )
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'package version'
            )
            ->addOption(
                'dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Specify a custom directory path for the project. By default, project will be installed in the same directory as the project name.'
            )
            ->addOption(
                'repo', //todo: test
                'r',
                InputOption::VALUE_REQUIRED,
                'Provide a custom repository to search for the package, which will be used instead of packagist.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $microTime = microtime(true);
        $this->getTask()->execute($input, $output);
        $output->writeln(
            'Generated by Samurai in ' . number_format(microtime(true) - $microTime, 2, '.', ' ') . ' sec. Banzai!'
        );
    }
}

