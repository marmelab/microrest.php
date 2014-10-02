<?php

namespace Marmelab\Silex\Provider\Silrest\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class RestController
{
    protected $restconfig;

    public function __construct($restconfig)
    {
        $this->restconfig = $restconfig;
    }

    public function getAction()
    {
        return new JsonResponse($this->restconfig);
    }
}
