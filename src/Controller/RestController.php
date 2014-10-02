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

    public function getListAction($objectType)
    {
        return new JsonResponse("This is a get action for List of object type $objectType");
    }
    public function postListAction($objectType)
    {
        return new JsonResponse("This is a post action for List of object type $objectType");
    }
    public function getObjectAction($objectId, $objectType)
    {
        return new JsonResponse("This is a get action for Object type $objectType with id " . $objectId);
    }
    public function putObjectAction($objectId, $objectType)
    {
        return new JsonResponse("This is a put action for Object type $objectType with id " . $objectId);
    }
    public function patchObjectAction($objectId, $objectType)
    {
        return new JsonResponse("This is a patch action for Object type $objectType with id " . $objectId);
    }
    public function deleteObjectAction($objectId, $objectType)
    {
        return new JsonResponse("This is a delete action for Object type $objectType with id " . $objectId);
    }
}
