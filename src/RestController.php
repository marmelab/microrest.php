<?php

namespace Marmelab\Microrest;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestController
{
    protected $dbal;

    public function __construct(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    public function homeAction($availableRoutes)
    {
        return new JsonResponse($availableRoutes);
    }

    public function getListAction($objectType)
    {
        try {
            $objects = $this->dbal->fetchAll('SELECT * FROM '.$objectType);
        } catch (\Exception $e) {
            $objects = array('error' => $e->getMessage());
        }

        return new JsonResponse($objects);
    }

    public function postListAction($objectType, Request $request)
    {
        $this->dbal->insert($objectType, $request->request->all());
        $id = $this->dbal->lastInsertId();

        return $this->getObjectResponse($objectType, $id, 201);
    }

    public function getObjectAction($objectId, $objectType)
    {
        return $this->getObjectResponse($objectType, $objectId);
    }

    public function putObjectAction($objectId, $objectType, Request $request)
    {
        $id = $request->request->get('id');
        $request->request->remove('id');
        $data = $request->request->all();

        $this->dbal->update($objectType, $data, array('id' => $id));

        return $this->getObjectResponse($objectType, $id);
    }

    public function deleteObjectAction($objectId, $objectType)
    {
        $this->dbal->delete($objectType, array('id' => $objectId));

        return new JsonResponse('', 204);
    }

    private function getObjectResponse($name, $id, $status = 200)
    {
        $sql = "SELECT * FROM $name WHERE id = ?";
        $object = $this->dbal->fetchAssoc($sql, array((int) $id));

        return new Response(json_encode($object), $status);
    }
}
