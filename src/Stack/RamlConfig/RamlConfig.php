<?php

namespace Marmelab\Microrest\Stack\RamlConfig;

use Raml\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RamlConfig implements HttpKernelInterface
{
    private $app;
    private $definition;

    public function __construct(HttpKernelInterface $app, array $options = [])
    {
        $this->app = $app;

        if (!is_readable($configFile = $options['path'])) {
            throw new \RuntimeException('RAML config file is not readable');
        }

        $this->definition = (new Parser())->parse($configFile);
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $request->attributes->set('raml.config', $this->definition);

        return $this->app->handle($request, $type, $catch);
    }
}
