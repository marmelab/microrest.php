<?php

namespace Marmelab\Microrest;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RouteBuilder
{
    private static $validMethods = array('GET', 'POST', 'PUT', 'PATCH', 'DELETE');

    public function build($controllers, array $routes, $controllerService, $contentType = 'application/json')
    {
        $availableRoutes = array();
        $beforeMiddleware = function (Request $request, Application $app) use ($contentType) {
            if (0 === strpos($request->headers->get('Content-Type'), $contentType)) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        };
        $afterMiddleware = function (Request $request, Response $response, Application $app) use ($contentType) {
            $request->headers->set('Content-Type', $contentType);
            $request->headers->set('Accept', $contentType);
        };

        foreach ($routes as $index => $route) {
            if (!in_array($route['method'], self::$validMethods)) {
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
            $name = 'microrest.'.strtolower($route['method']).ucfirst($route['objectType']).$route['type'];

            $controllers
                ->match($route['path'], $action)
                ->method($route['method'])
                ->setDefault('objectType', $route['objectType'])
                ->bind($name)
                ->before($beforeMiddleware)
                ->after($afterMiddleware)
            ;
        }

        $controllers->match('/', $controllerService.':homeAction')
            ->method('GET')
            ->setDefault('availableRoutes', $availableRoutes)
            ->bind('microrest.home')
            ->after($afterMiddleware)
        ;

        return $controllers;
    }
}
