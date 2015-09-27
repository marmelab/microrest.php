<?php

namespace Marmelab\Microrest\Stack\RouteGenerator;

use Silex\Application;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Marmelab\Microrest\RestController;
use Marmelab\Microrest\RouteBuilder;


class RouteGenerator implements HttpKernelInterface
{
    private $microrestApp;
    private $app;
    private $request;
    private $type;
    private $catch;

    public function __construct(HttpKernelInterface $app, array $options = [])
    {
        $this->app = $app;
        $this->microrestApp = $this->getMicrorestApp($options);
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->request = $request;
        $this->type = $type;
        $this->catch = $catch;

        $definition = $request->attributes->get('raml.config');
        $this->configureMicrorest($definition);

        $microrestResponse = $this->microrestApp->handle($request, $type, $catch);

        $response = $this->app->handle($request, $type, $catch);

        if (404 !== $microrestResponse->getStatusCode()) {
            // $this->microrestApp->terminate($request, $microrestResponse); // Todo ?

            $response->setContent($microrestResponse->getContent());
            $response->headers->set('content-type', $microrestResponse->headers->get('content-type'));
            if ($totalCount = $microrestResponse->headers->get('X-total-Count')) {
                $response->headers->set('X-total-Count', $totalCount);
            }
        }

        return $response;

    }

    private function configureMicrorest($definition)
    {
        $this->microrestApp['microrest.mediaType'] = $definition->getDefaultMediaType() ?: 'application/json';

        $routes = $definition
            ->getResourcesAsUri()
            ->getRoutes()
        ;

        $baseUrl = $definition->getBaseUrl();
        $this->apiUrlPrefix = $baseUrl ? substr(parse_url($baseUrl, PHP_URL_PATH), 1) : 'api';

        $this->microrestApp['microrest.restController'] = $this->microrestApp->share(function () {
            return new RestController($this->microrestApp['db']);
        });

        $controllers = (new RouteBuilder())
            ->build($this->microrestApp['controllers_factory'], $routes, 'microrest.restController', true);
        $this->microrestApp['controllers']->mount($this->apiUrlPrefix, $controllers);

        $this->microrestApp->error(function(\Exception $e, $code) {
            //var_dump($e); die;
            return new JsonResponse(['error' => $e->getMessage()], $code);
        });
    }

    private function getMicrorestApp(array $config = [])
    {
        $app = new Application();
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new DoctrineServiceProvider(), array(
            'db.options' => $config['db.options'],
        ));

        return $app;
    }
}
