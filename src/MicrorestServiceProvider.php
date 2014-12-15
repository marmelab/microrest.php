<?php

namespace Marmelab\Microrest;

use Raml\Parser;
use Silex\Application;
use Silex\ServiceProviderInterface;

class MicrorestServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['microrest.url_prefix'] = 'api';

        $app['microrest.restController'] = $app->share(function () use ($app) {
            return new RestController($app['db']);
        });

        $app['microrest.routeBuilder'] = $app->share(function () {
            return new RouteBuilder();
        });
    }

    public function boot(Application $app)
    {
        if (!is_readable($configFile = $app['microrest.config_file'])) {
            throw new \RuntimeException("API config file is not readable");
        }
        $routes = (new Parser())
            ->parse($configFile)
            ->getResourcesAsUri()
            ->getRoutes()
        ;

        $controllers = $app['microrest.routeBuilder']
            ->build($app['controllers_factory'], $routes, 'microrest.restController');
        $app['controllers']->mount($app['microrest.url_prefix'], $controllers);
    }
}
