<?php

namespace Marmelab\Silrest\Config;

use Marmelab\Silrest\Parser\SilrestConfigParser;

class ValidConfig
{
    protected $config;

    public function __construct($configFile, SilrestConfigParser $parser)
    {
        if (!file_exists($configFile) || !is_readable($configFile) || is_dir($configFile)) {
            throw new \Exception("api config file is unreachable");
        }
        $this->config = $parser->parse($configFile);
    }

    public function getConfig()
    {
        return $this->config->getResourcesAsUri()->getRoutes();
    }
}
