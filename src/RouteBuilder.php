<?php

namespace Marmelab\Microrest;

use Silex\Application;

class RouteBuilder
{
    private static $validMethod = array ('get', 'post', 'put', 'patch', 'delete');

    public function build($controllers, array $routes, $controllerService)
    {
        $availableRoutes = array();

        foreach ($routes as $index => $route) {
            if (!in_array(strtolower($route['method']), self::$validMethod)) {
                continue;
            }

            $availableRoutes[] = $index;

            if (preg_match('/{[\w-]+}/', $route['path'], $identifier)) {
                $route['type'] = 'Object';
                $route['objectType'] = strtolower(str_replace(array('/', $identifier[0]), '', $route['path']));
                $route['path'] = str_replace($identifier[0], '{objectId}', $route['path']);
            } else {
                $route['type'] = 'List';
                $route['objectType'] = strtolower(str_replace('/', '', $route['path']));
            }

            $action = $controllerService.':'.strtolower($route['method']).$route['type'].'Action';
            $name = 'microrest.'.strtolower($route['method']).$route['objectType'].$route['type'];

            $controllers
                ->match($route['path'], $action)
                ->method($route['method'])
                ->setDefault('objectType', $route['objectType'])
                ->bind($name)
            ;
        }

        $controllers->match('/', $controllerService.':homeAction')
            ->method('GET')
            ->setDefault('availableRoutes', $availableRoutes)
            ->bind('microrest.home');

        return $controllers;
    }
}
