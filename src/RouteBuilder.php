<?php

namespace Marmelab\Microrest;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RouteBuilder
{
    public static $validMethods = array('GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS');

    public function build($controllers, array $routes, $controllerService, $addOptionsRoute = false)
    {
        $availableRoutes = array();
        $beforeMiddleware = function (Request $request, Application $app) {
            if (0 === strpos($request->headers->get('Content-Type'), $app['microrest.mediaType'])) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        };
        $afterMiddleware = function (Request $request, Response $response, Application $app) {
            $response->headers->set('Content-Type', $app['microrest.mediaType']);
        };

        foreach ($routes as $index => $route) {
            $route['method'] = $route['type'];
            unset($route['type']);

            if (!in_array($route['method'], self::$validMethods)) {
                continue;
            }

            if (2 < substr_count($route['path'], '/')) { // only handle simple routes (/example or /example/{id})
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

            if ($addOptionsRoute) {
                $controllers
                    ->match($route['path'], function () use ($route) {
                        return new Response('', 204, ['Allow' => [$route['method'], 'OPTIONS']]);
                    })
                    ->method('OPTIONS')
                ;
            }
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
