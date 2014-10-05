<?php

namespace Marmelab\Silex\Provider\Silrest\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection as dbConnexion;

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
        if (!self::tableExist($objectType)) {
            self::createTable($objectType);

            return new JsonResponse(array());
        }
        $sql = 'SELECT * FROM ?';
        try {
            $objects = $this->dbal->fetchAll('SELECT * FROM ' . $objectType);
        } catch (\Exception $e) {
            $objects = array("error" => $e->getMessage());
        }

        return new JsonResponse($objects);
    }
    public function postListAction($objectType, Request $request)
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
        $newid = $this->dbal->insert($objectType, array('content' => $request->get('content')));

        return new JsonResponse("Object created $newid", 201);
    }
    public function getObjectAction($objectId, $objectType)
    {
        $sql = "SELECT * FROM $objectType WHERE id = ?";
        $object = $this->dbal->fetchAssoc($sql, array((int) $objectId));

        return new JsonResponse(json_encode($object), 200);
    }
    public function putObjectAction($objectId, $objectType, Request $request)
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }
        $this->dbal->update($objectType, array('content' => $request->get('content')), array('id' => $objectId));

        return new JsonResponse("This is a put action for Object type $objectType with id " . $objectId);
    }

    public function deleteObjectAction($objectId, $objectType)
    {
        $this->dbal->delete($objectType, array('id' => $objectId));

        return new JsonResponse("This is a delete action for Object type $objectType with id " . $objectId);
    }

    private function tableExist($tableName)
    {
        $sql = "CREATE TABLE " . $tableName. " ( id INT)";
        try {
            $this->dbal->prepare($sql);
        } catch (\Exception $e) {
            return true;
        }

        return false;
    }

    private function createTable($tableName)
    {
        //TODO works only with SQLITE; dbal is not very useful here ...
        $sql = "CREATE TABLE '".$tableName."' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'content' TEXT)";
        $stmt = $this->dbal->prepare($sql);
        $stmt->execute();
    }
}
