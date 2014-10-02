<?php

namespace Marmelab\Silex\Provider\Silrest\Config;

use Silex\Application;

class BuildConfig
{
    protected $app;
    protected $config;

    public function __construct(Application $app, $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function buildRouting()
    {
        return false;
    }
}
