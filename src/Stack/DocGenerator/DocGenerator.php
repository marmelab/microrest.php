<?php

namespace Marmelab\Microrest\Stack\DocGenerator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DocGenerator implements HttpKernelInterface
{
    private $app;
    private $definition;

    public function __construct(HttpKernelInterface $app, array $options = [])
    {
        $this->app = $app;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->definition = $request->attributes->get('raml.config');
        $response = $this->app->handle($request, $type, $catch);
        // Render twig views/doc.html.twig, genereted with webpack from src, with definition as parameter

        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/views');
        $twig = new \Twig_Environment($loader);
        $description = $this->getFormatedDescription();
        $html = $twig->render('doc.html.twig', $description);
        $response = new Response($html);
        return $response;
    }

    private function getFormatedDescription ()
    {
        $resources = array();
        foreach ($this->definition->getResources() as $resource) {
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
            'apiName' => $this->definition->getTitle(),
            'resources' => $resources,
            'baseUrl' => $this->definition->getBaseUrl(),
            'version' => $this->definition->getVersion(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(Request $request, Response $response)
    {
        $this->kernel->terminate($request, $response);
    }
}
