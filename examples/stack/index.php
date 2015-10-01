<?php
require_once __DIR__.'/vendor/autoload.php';

use Asm89\Stack\Cors;
use Marmelab\Microrest\Stack\RamlConfig\RamlConfig;
use Marmelab\Microrest\Stack\RouteGenerator\RouteGenerator;
use Marmelab\Microrest\Stack\DocGenerator\DocGenerator;
use Marmelab\Microrest\RouteBuilder;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainApp implements HttpKernelInterface
{
    public function handle(Symfony\Component\HttpFoundation\Request $request, $type = self::MASTER_REQUEST, $catch = true) {
        return new Response('Marmelab Microrest.php');
    }
}

$mainApp = new MainApp();
$stack = (new Stack\Builder())
    ->push(Cors::class, [
            'allowedHeaders' => ['x-total-count'],
            'allowedMethods' => RouteBuilder::$validMethods,
            'allowedOrigins' => ['*'],
            'exposedHeaders' => true,
            ])
    ->push(RamlConfig::class, [
        'path' => __DIR__.'/api.raml',
    ])
    ->push(RouteGenerator::class, [
        'db.options' => [
            'driver' => 'pdo_sqlite',
            'path' => __DIR__.'/app.db',
        ],
    ])
    ->push('Stack\UrlMap', [
        "/doc" => new DocGenerator($mainApp)
    ]);

$app = $stack->resolve($mainApp);

Stack\run($app);
