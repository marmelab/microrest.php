<?php

namespace Marmelab\Silex\Provider\Silrest\Config;

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
        foreach ($this->config as $index => $value) {
            if (strpos($index, '/') !== false) {
                self::browseCollection($index, $value);
            }
        }
        $this->app['controllers']->mount($this->app['rest_api.url_prefixe'], $this->controllers);
    }

    private function browseCollection($collectionName, array $collectionDatas, $parent = null)
    {
        foreach ($collectionDatas as $index => $value) {
            if (strpos($index, '/') !== false) {
                self::browseCollection($index, $value, $collectionName);
            }
        }
        self::addCollectionRouting($parent, $collectionName, $collectionDatas);
    }

    private function addCollectionRouting($parent, $collectionName, array $collectionDatas)
    {
        $routesParam = array (
            'parent_path' => $parent,
            'path' => null,
            'base_name' => null,
            'type' => null,
            'objectId' => null,
            'objectType' => null,
            'methods' => array()
        );
        if (preg_match("/^\/\{(\w*)\}$/", $collectionName, $identifier)) {
            $routesParam['type'] = 'Object';
            $routesParam['objectId'] = $identifier[1];
            $routesParam['base_name'] = str_replace('/', '', $parent); //TODO this is not necessary true !?
            $routesParam['path'] = $parent . '/{objectId}'; //TODO this is not necessary true !? In case with subCollaction as artist/{artistId}/albums
            $routesParam['objectType'] = strtolower(str_replace('/', '', $parent));
        } else {
            $routesParam['type'] = 'List';
            $routesParam['base_name'] = str_replace('/', '', $collectionName);
            $routesParam['path'] = $collectionName; //TODO this is not necessary true !? In case with subCollaction as artist/{artistId}/albums
            $routesParam['objectType'] = strtolower(str_replace('/', '', $collectionName));
        }
        foreach ($collectionDatas as $index => $value) {
            if (in_array($index, $this->validMethod) && $value != null ) {
                $method = array (
                    'name' => $index,
                    'options' => $value
                );
                $routesParam['methods'][] = $method;
            }
        }
        self::createRoutesForCollection($routesParam);
    }

    private function createRoutesForCollection(array $collectionRoutingDatas)
    {
        $controllerService = 'rest_api.restController:';
        foreach ($collectionRoutingDatas['methods'] as $method) {
            $this->controllers->match($collectionRoutingDatas['path'], $controllerService . $method['name'] . $collectionRoutingDatas['type'] . 'Action')
                         ->method(strtoupper($method['name']))
                         ->setDefault("objectType", $collectionRoutingDatas['objectType'])
                         ->setDefault("options", $method['options'])
                         ->bind(strtolower($collectionRoutingDatas['base_name'] . $method['name'] . $collectionRoutingDatas['type'] . 'Action'));
        }
    }
}
