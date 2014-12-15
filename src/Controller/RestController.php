<?php

namespace Marmelab\Silrest\Controller;

use Doctrine\DBAL\Connection as dbConnexion;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestController
{
    protected $restconfig;

    public function __construct($restconfig, dbConnexion $dbal)
    {
        $this->restconfig = $restconfig;
        $this->dbal = $dbal;
    }

    public function homeAction($firstClassObjects)
    {
        return new JsonResponse($firstClassObjects);
    }

    public function getListAction($objectType)
    {
        try {
            $objects = $this->dbal->fetchAll('SELECT * FROM ' . $objectType);
        } catch (\Exception $e) {
            $objects = array("error" => $e->getMessage());
        }

        return new JsonResponse($objects);
    }
    public function postListAction($objectType, Request $request)
    {
        $newid = $this->dbal->insert($objectType, array('content' => $request->get('content')));

        return new JsonResponse("Object created $newid", 201);
    }

    public function getObjectAction($objectId, $objectType)
    {
        $sql = "SELECT * FROM $objectType WHERE id = ?";
        $object = $this->dbal->fetchAssoc($sql, array((int) $objectId));

        return new Response(json_encode($object), 200);
    }

    public function putObjectAction($objectId, $objectType, Request $request)
    {
        $this->dbal->update($objectType, array('content' => $request->get('content')), array('id' => $objectId));

        return new JsonResponse("This is a put action for Object type $objectType with id " . $objectId);
    }

    public function deleteObjectAction($objectId, $objectType)
    {
        $this->dbal->delete($objectType, array('id' => $objectId));

        return new JsonResponse("This is a delete action for Object type $objectType with id " . $objectId);
    }
}
