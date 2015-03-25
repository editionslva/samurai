<?php
namespace Samurai\Alias;

use ArrayObject;
use Puppy\Config\Config;

/**
 * Class AliasManager
 * @package Samurai\alias
 * @author Raphaël Lefebvre <raphael@raphaellefebvre.be>
 */
class AliasManager
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param ArrayObject $config
     */
    public function __construct(ArrayObject $config)
    {
        $this->setConfig($config);
    }

    /**
     * @param $name
     * @return null|Alias
     */
    public function get($name)
    {
        if($this->has($name)){
            return $this->getAll()[$name];
        }
        return null;
    }

    /**
     * @return Alias[]
     */
    public function getAll()
    {
        return array_merge($this->getGlobal(), $this->getLocal());
    }

    /**
     * @return Alias[]
     */
    public function getGlobal()
    {
        return $this->retrieveFrom('alias.global.path');
    }

    /**
     * @return Alias[]
     */
    public function getLocal()
    {
        return $this->retrieveFrom('alias.local.path');
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->getAll());
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasGlobal($name)
    {
        return array_key_exists($name, $this->getGlobal());
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasLocal($name)
    {
        return array_key_exists($name, $this->getLocal());
    }

    /**
     * @param array $aliasList
     * @return int
     */
    public function addList(array $aliasList)
    {
        $this->createLocalConfigFile($this->getConfig()['alias.local.path']);
        return file_put_contents(
            $this->getConfig()['alias.local.path'],
            json_encode(
                $this->unmap($aliasList),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
    }

    /**
     * @param Alias $alias
     * @return int
     */
    public function add(Alias $alias)
    {
        $aliasList = $this->getLocal();
        $aliasList[$alias->getName()] = $alias;
        return $this->addList($aliasList);
    }

    /**
     * @param string $name
     * @return int
     */
    public function remove($name)
    {
        $aliasList = $this->getLocal();
        unset($aliasList[$name]);
        return $this->addList($aliasList);
    }

    /**
     * Getter of $config
     *
     * @return ArrayObject
     */
    private function getConfig()
    {
        return $this->config;
    }

    /**
     * Setter of $config
     *
     * @param ArrayObject $config
     */
    private function setConfig(ArrayObject $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $aliasList
     * @return Alias[]
     */
    private function map(array $aliasList)
    {
        $result = [];
        foreach($aliasList as $aliasData){
            $alias = new Alias();
            $alias->setName($aliasData['name']);
            $alias->setDescription($aliasData['description']);
            $alias->setBootstrap($aliasData['bootstrap']);
            $alias->setVersion($aliasData['version']);
            $result[$alias->getName()] = $alias;
        }
        return $result;
    }

    /**
     * @param Alias[] $aliasList
     * @return array
     */
    private function unmap(array $aliasList)
    {
        $result = [];
        foreach($aliasList as $alias){
            $result[$alias->getName()] = $alias->toArray();
        }
        return $result;
    }

    /**
     * @param string $key
     * @return Alias[]
     */
    private function retrieveFrom($key)
    {
        if(!file_exists($this->getConfig()[$key]) || !is_readable($this->getConfig()[$key])){
            return [];
        }

        $json = json_decode(
            file_get_contents($this->getConfig()[$key]),
            true
        );
        return $this->map($json ? : []);
    }

    /**
     * @param $path
     */
    private function createLocalConfigFile($path)
    {
        $currentPath = '';
        $subPathList = explode('/', $path);
        foreach($subPathList as $index => $subPath) { //we force to use / because project name separator is only /
            $currentPath .= $subPath . DIRECTORY_SEPARATOR;
            if($index < count($subPathList) - 1 && !file_exists($currentPath)){
                mkdir($currentPath);
            }
        }
    }
}