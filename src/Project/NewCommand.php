<?php
namespace Samurai\Project;

use Samurai\Command\Command;
use Samurai\Project\Task\Factory\BootstrapImportationTaskFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NewCommand
 * @package Samurai\Project
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class NewCommand extends Command
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
                'package name'
            )
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'package version'
            )
            ->addArgument(
                'source',
                InputArgument::OPTIONAL,
                'package source'
            )
            ->addOption(
                'dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Specify a custom directory path for the project. By default, project will be installed in the same directory as the project name.'
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

    /**
     * @return \Samurai\Task\ITask
     */
    private function getTask()
    {
        $factory = new BootstrapImportationTaskFactory();
        return $factory->create($this->getServices());
    }
}

