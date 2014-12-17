<?php

namespace Marmelab\Microrest;

use Raml\Parser;
use Silex\Application;
use Silex\ServiceProviderInterface;

class MicrorestServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['microrest.initializer'] = $app->protect(function () use ($app) {
            if (!is_readable($configFile = $app['microrest.config_file'])) {
                throw new \RuntimeException("API config file is not readable");
            }
            $config = (new Parser())->parse($configFile);

            $app['microrest.mediaType'] = $config->getDefaultMediaType() ?: 'application/json';

            $app['microrest.routes'] = $config
                ->getResourcesAsUri()
                ->getRoutes()
            ;

            $baseUrl = $config->getBaseUrl();
            $app['microrest.url_prefix'] = $baseUrl ? substr(parse_url($baseUrl, PHP_URL_PATH), 1) : 'api';

            $app['microrest.restController'] = $app->share(function () use ($app) {
                return new RestController($app['db']);
            });

            $app['microrest.routeBuilder'] = $app->share(function () {
                return new RouteBuilder();
            });
        });

        $app['microrest.builder'] = function () use ($app) {
            $app['microrest.initializer']();

            $controllers = $app['microrest.routeBuilder']
                ->build($app['controllers_factory'], $app['microrest.routes'], 'microrest.restController');
            $app['controllers']->mount($app['microrest.url_prefix'], $controllers);
        };
    }

    public function boot(Application $app)
    {
        $app['microrest.builder'];
    }
}
