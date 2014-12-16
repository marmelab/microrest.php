<?php

namespace Marmelab\Microrest;

use Doctrine\DBAL\Connection;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Pagerfanta\Pagerfanta;
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

    public function getListAction($objectType, Request $request)
    {
        $queryBuilder = $this->dbal
            ->createQueryBuilder()
            ->select('o.*')
            ->from($objectType, 'o')
        ;

        if ($sort = $request->query->get('_sort')) {
            $queryBuilder->orderBy($sort, $request->query->get('_sortDir', 'ASC'));
        }

        $countQueryBuilderModifier = function ($queryBuilder) {
            $queryBuilder
                ->select('COUNT(DISTINCT o.id) AS total_results')
                ->setMaxResults(1)
            ;
        };

        $pager = new DoctrineDbalAdapter($queryBuilder, $countQueryBuilderModifier);

        try {
            $nbResults = $pager->getNbResults();
            $results = $pager->getSlice($request->query->get('_start', 0), $request->query->get('_end', 20));
        } catch (\Exception $e) {
            $results = array('error' => $e->getMessage());
        }

        return new JsonResponse($results, 200, array(
            'X-Total-Count' => $nbResults,
        ));
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
