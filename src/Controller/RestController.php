<?php

namespace Marmelab\Silex\Provider\Silrest\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
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
        $availableApi = array();
        $tablesToCreate = array();
        foreach ($firstClassObjects as $object) {
            $sql = "CREATE TABLE " . $object . " ( id INT, body STRING)";
            try {
                $stmt = $this->dbal->prepare($sql);
                $tablesToCreate[] = $object;
             } catch (\Exception $e) {
                 $availableApi[] = $object;
             }
        }
        $message = array ();
        if (count($availableApi)) {
             $message["available_documents"] = $availableApi;
        }
        if (count($tablesToCreate)) {
             $message["tables_to_create"] = $tablesToCreate;
             $message["tables_ceration_link"] = "/create_database_tables";
        }

        return new JsonResponse($message);
    }

    public function getListAction($objectType)
    {
        $sql = 'SELECT * FROM ?';
        try {
            $objects = $this->dbal->fetchAll('SELECT * FROM ' . $objectType);
        } catch (\Exception $e) {
            $objects = array("error" => $e->getMessage());
        }

        return new JsonResponse($objects);
    }
    public function postListAction($objectType)
    {
        $this->dbal->insert($objectType, array('content' => 'je suis une truffe'));

        return new JsonResponse("Object created");
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
    public function createDbAction($dbTables)
    {
        $createdTables = array();
        $existingTables = array();
        foreach ($dbTables as $table) {
            //TODO works only with SQLITE; dbal is not very useful here ...
            $sql = "CREATE TABLE '".$table."' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'content' TEXT)";
            try {
                $stmt = $this->dbal->prepare($sql);
                $stmt->execute();
                $createdTables[] = $table;
            } catch (\Exception $e) {
                $existingTables[] = $table;
            }
        }
        $message = array (
            "existing_tables" => $existingTables,
            "created_tables" => $createdTables
        );

        return new JsonResponse($message);
    }
}
