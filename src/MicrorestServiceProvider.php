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

            $apiDefinition = (new Parser())->parse($configFile);

            $app['microrest.mediaType'] = $apiDefinition->getDefaultMediaType() ?: 'application/json';

            $app['microrest.routes'] = $apiDefinition
                ->getResourcesAsUri()
                ->getRoutes()
            ;

            $baseUrl = $apiDefinition->getBaseUrl();
            $app['microrest.api_url_prefix'] = $baseUrl ? substr(parse_url($baseUrl, PHP_URL_PATH), 1) : 'api';

            $app['microrest.restController'] = $app->share(function () use ($app) {
                return new RestController($app['db']);
            });

            $app['microrest.routeBuilder'] = $app->share(function () {
                return new RouteBuilder();
            });

            $app['microrest.doc_url_prefix'] = 'doc';

            $app['microrest.description'] = function () use ($apiDefinition) {
                $resources = array();
                foreach ($apiDefinition->getResources() as $resource) {
                    $endpoints = array();
                    foreach ($resource->getMethods() as $method) {
                        $endpoints[] = [
                            'uri' => $resource->getUri(),
                            'method' => $method->getType(),
                            'description' => $method->getDescription(),
                        ];
                    }

                    foreach ($resource->getResources() as $endpoint) {
                        foreach ($endpoint->getMethods() as $method) {
                            $endpoints[] = [
                                'uri' => $endpoint->getUri(),
                                'method' => $method->getType(),
                                'description' => $method->getDescription(),
                            ];
                        }
                    }

                    $resources[] = [
                        'uri' => $resource->getUri(),
                        'name' => $resource->getDisplayName(),
                        'description' => $resource->getDescription(),
                        'endpoints' => $endpoints,
                    ];
                }

                return array(
                    'apiName' => $apiDefinition->getTitle(),
                    'resources' => $resources,
                    'baseUrl' => $apiDefinition->getBaseUrl(),
                    'version' => $apiDefinition->getVersion(),
                );
            };

            $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function ($loader, $app) {
                $loader->addPath($app['microrest.templates_path'], 'Microrest');

                return $loader;
            }));

            $app['microrest.templates_path'] = function () {
                return __DIR__.'/../views';
            };
        });

        $app['microrest.builder'] = function () use ($app) {
            $app['microrest.initializer']();

            $controllers = $app['microrest.routeBuilder']
                ->build($app['controllers_factory'], $app['microrest.routes'], 'microrest.restController');
            $app['controllers']->mount($app['microrest.api_url_prefix'], $controllers);

            $controllers = $app['controllers_factory'];
            $controllers->get('/', function () use ($app) {
                return $app['twig']->render('@Microrest/doc.html.twig', $app['microrest.description']);
            });

            $app['controllers']->mount($app['microrest.doc_url_prefix'], $controllers);
        };
    }

    public function boot(Application $app)
    {
        $app['microrest.builder'];
    }
}
