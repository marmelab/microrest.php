<?php

namespace Marmelab\Silrest\Config;

use Silex\Application;

class BuildConfig
{
    protected $app;
    protected $config;
    protected $validMethod = array ('get', 'post', 'put', 'patch', 'delete');
    protected $controllers;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->controllers = $app['controllers_factory'];
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function buildRouting()
    {
        $firstClassObjects = array();
        foreach ($this->config as $index => $value) {
            $firstClassObjects[] = $index;
            self::addCollectionRouting($index, $value);
        }

        self::buildHomePage($firstClassObjects);

        $this->app['controllers']->mount($this->app['rest_api.url_prefixe'], $this->controllers);
    }

    private function buildHomePage(array $firstClassObjects)
    {
        $this->controllers->match('/', 'rest_api.restController:homeAction')
             ->method('GET')
             ->setDefault("firstClassObjects", $firstClassObjects)
             ->bind('silrest.home');
    }

    private function addCollectionRouting($collectionName, array $collectionDatas)
    {
        $routesParam = array (
            'method' => $collectionDatas['method'],
            'path' => $collectionDatas['path'],
            'type' => null,
            'objectType' => null,
        );

        if (preg_match("/{[\w-]+}/", $routesParam['path'], $identifier)) {
            //dump($identifier); die;
            $routesParam['type'] = 'Object';
            $routesParam['objectType'] = strtolower(str_replace(array('/', $identifier[0]), '', $collectionDatas['path']));
            $routesParam['path'] = str_replace($identifier[0], '{objectId}', $routesParam['path']);
        } else {
            $routesParam['type'] = 'List';
            $routesParam['objectType'] = strtolower(str_replace('/', '', $collectionDatas['path']));
        }

        $controllerService = 'rest_api.restController:';
        $this->controllers
            ->match($routesParam['path'], $controllerService . strtolower($routesParam['method']) . $routesParam['type'] . 'Action')
            ->method($routesParam['method'])
            ->setDefault("objectType", $routesParam['objectType'])
        ;

        //dump($routesParam, $controllerService . strtolower($routesParam['method']) . $routesParam['type'] . 'Action');
    }

    private function createRoutesForCollection(array $collectionRoutingDatas)
    {
        
    }
}
