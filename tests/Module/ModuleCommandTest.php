<?php
namespace Samurai\Module;

use Balloon\Factory\BalloonFactory;
use Pimple\Container;
use Puppy\Config\Config;
use Samurai\Module\Factory\ModuleManagerFactory;
use Samurai\Project\Composer\Composer;
use Samurai\Samurai;
use Samurai\Task\ITask;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use TRex\Cli\Executor;

/**
 * Class ModuleCommandTest
 * @package Samurai\Module
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class ModuleCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteSave()
    {

        $executor = $this->getMock('TRex\Cli\executor');
        $executor->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(0));

        $application = new Application();
        $samurai = new Samurai($application, $this->provideServices($application, $executor));

        $this->assertNull($samurai->getServices()['module_manager']->get('test'));


        $command = $samurai->getApplication()->find('module');
        $commandTester = new CommandTester($command);
        $this->assertSame(ITask::NO_ERROR_CODE, $commandTester->execute([
            'command' => $command->getName(),
            'action' => 'save',
            'name' => 'test',
            'package' => 'vendor/package',
            'version' => '@stable',
            'description' => 'description',
        ]));

        $this->assertNotNull($samurai->getServices()['module_manager']->get('test'));
        $this->assertSame(
            "Starting installation of vendor/package.\nSorting modules.\n",
            $commandTester->getDisplay(true)
        );
    }

    /**
     * @depend testExecuteSave
     */
    public function testExecuteListAll()
    {
        $samurai = new Samurai(new Application());

        $this->assertNotNull($samurai->getServices()['module_manager']->get('test'));

        $command = $samurai->getApplication()->find('module');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'action' => 'list',
        ]);

        $this->assertStringStartsWith(
            "1 module(s) set:\n\nname: test\ndescription: description\npackage: vendor/package\nversion: @stable\nsource: \nisEnable: 1\n",
            $commandTester->getDisplay(true)
        );
    }


    private function provideServices(Application $application, Executor $executor)
    {
        $services = new Container();

        $services['executor'] = function () use ($executor){
            return $executor;
        };

        $services['composer'] = function (Container $services) {
            return new Composer($services['executor'], new BalloonFactory());
        };

        $services['helper_set'] = function () use ($application) {
            return $application->getHelperSet();
        };

        $services['config'] = function () {
            return new Config('');
        };

        $services['module_manager'] = function (Container $services) {
            $factory = new ModuleManagerFactory();
            return $factory->create($services['config']['module.path']);
        };

        $services['module_importer'] = function (Container $services) {
            return new ModuleImporter(
                $services['module_manager'],
                $services['composer'],
                $services['balloon_factory'],
                new ModulesSorter()
            );
        };

        $services['balloon_factory'] = function () {
            return new BalloonFactory();
        };

        return $services;
    }

}
