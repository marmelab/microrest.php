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
        $this->definition = $request->attributes->get('raml.config');
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $response = $this->app->handle($request, $type, $catch);
        // Render twig views/doc.html.twig, genereted with webpack from src, with definition as parameter
        $response = new Response('API DOCUMENTATION');
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(Request $request, Response $response)
    {
        $this->kernel->terminate($request, $response);
    }
}
