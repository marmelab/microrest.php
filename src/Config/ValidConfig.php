<?php

namespace Marmelab\Silex\Provider\Silrest\Config;

class ValidConfig
{
    protected $configFile;

    public function __construct($configFile)
    {
        //TODO use cache
        $this->configFile = $configFile;
    }

    public function getConfig()
    {
        return true;
    }
}
