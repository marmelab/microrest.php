<?php

namespace Marmelab\Silex\Provider\Silrest;

use Silex\Application;
use Silex\ServiceProviderInterface;

class RestApiProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'rest_api.restController:getAction')
            ->method('GET')
            ->bind('objectname.actionname');

        $app['controllers']->mount($app['rest_api.url_prefixe'], $controllers);
    }

    public function register(Application $app)
    {
        $app['rest_api.restController'] = $app->share(function () use ($app) {
            return new Controller\RestController($app['rest_api.config_file']);
        });
    }

}
