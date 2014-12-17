<?php

use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Marmelab\Microrest\MicrorestServiceProvider;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());

$app->register(new MicrorestServiceProvider(), array(
    'microrest.config_file' => __DIR__ . '/../config/api/api.raml',
));

$app->register(new CorsServiceProvider(), array(
    'cors.allowOrigin' => 'http://localhost:8000',
    'cors.exposeHeaders' => 'X-Total-Count',
));

$app->after($app['cors']);

return $app;
