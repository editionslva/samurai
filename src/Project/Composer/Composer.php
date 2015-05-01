<?php
namespace Samurai\Project\Composer;

use InvalidArgumentException;
use Samurai\File\JsonFileManager;
use Samurai\Project\Project;
use TRex\Cli\Executor;

/**
 * Class Composer
 * @package Samurai\Project\Composer
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class Composer
{
    /**
     * @var JsonFileManager
     */
    private $composerConfigManager;

    /**
     * @var Executor
     */
    private $executor;

    /**
     * @param Executor $executor
     */
    public function __construct(Executor $executor)
    {
        $this->setComposerConfigManager(new JsonFileManager()); //todo DI => use pimple
        $this->setExecutor($executor);
    }

    /**
     * @param Project $project
     * @param array $options
     * @return string
     */
    public function createProject(Project $project, array $options = array())
    {
        if(!$project->getBootstrapName()){
            throw new InvalidArgumentException('The bootstrap of the project is not defined');
        }

        return $this->getExecutor()->flush(
            trim(
                sprintf(
                    'composer create-project --prefer-dist %s %s %s',
                    $project->getBootstrapName(),
                    $project->getDirectoryPath(),
                    $project->getBootstrapVersion()
                )
            )
            .$this->mapOptions($options)
        );
    }

    /**
     * @param $cwd
     * @return string
     */
    public function getConfigPath($cwd = '')
    {
        if($cwd){
            $cwd = rtrim($cwd, '/') . '/';
        }
        return  $cwd . 'composer.json';
    }

    /**
     * @param string $cwd
     * @return array|null
     */
    public function getConfig($cwd = '')
    {
        return $this->getComposerConfigManager()->get($this->getConfigPath($cwd));
    }

    /**
     * @param array $config
     * @param string $cwd
     * @return int
     */
    public function setConfig(array $config, $cwd = '')
    {
        return $this->getComposerConfigManager()->set($this->getConfigPath($cwd), $config);
    }

    /**
     * @param string $cwd
     * @return bool
     */
    public function validateConfig($cwd = '')
    {
        return $this->getExecutor()->flush($this->cd($cwd) . 'composer validate');
    }

    /**
     * @param string $cwd
     * @return bool
     */
    public function dumpAutoload($cwd = '')
    {
        return $this->getExecutor()->flush($this->cd($cwd) . 'composer dump-autoload');
    }

    /**
     * @param $cwd
     * @return string
     */
    private function cd($cwd)
    {
        if($cwd) {
            return 'cd ' . $cwd . ' && ';
        }
        return '';
    }

    /**
     * @param array $options
     * @return string
     */
    private function mapOptions(array $options)
    {
        $result = '';
        foreach($options as $option => $value){
            $result .= ' --' . $option . '=' . $value;
        }
        return $result;
    }

    /**
     * Getter of $composerConfigManager
     *
     * @return JsonFileManager
     */
    public function getComposerConfigManager()
    {
        return $this->composerConfigManager;
    }

    /**
     * Setter of $composerConfigManager
     *
     * @param JsonFileManager $composerConfigManager
     */
    public function setComposerConfigManager(JsonFileManager $composerConfigManager)
    {
        $this->composerConfigManager = $composerConfigManager;
    }

    /**
     * Getter of $executor
     *
     * @return Executor
     */
    private function getExecutor()
    {
        return $this->executor;
    }

    /**
     * Setter of $executor
     *
     * @param Executor $executor
     */
    private function setExecutor(Executor $executor)
    {
        $this->executor = $executor;
    }
}
