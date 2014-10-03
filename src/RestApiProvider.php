<?php

namespace Marmelab\Silex\Provider\Silrest;

use Silex\Application;
use Silex\ServiceProviderInterface;

class RestApiProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        $app['rest_api.builder']->setConfig($app['rest_api.configValidator']->getConfig());
        $app['rest_api.builder']->buildRouting();
    }

    public function register(Application $app)
    {
        $app['rest_api.restController'] = $app->share(function () use ($app) {
            return new Controller\RestController($app['rest_api.config_file'], $app['db']);
        });
        $app['rest_api.configParser'] = $app->share(function () {
            return new Parser\RamlParser();
        });
        $app['rest_api.configValidator'] = $app->share(function () use ($app) {
            return new Config\ValidConfig($app['rest_api.config_file'], $app['rest_api.configParser']);
        });
        $app['rest_api.builder'] = $app->share(function () use ($app) {
            return new Config\BuildConfig($app);
        });
    }
}
