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

        $nbResults = $pager->getNbResults();
        $results = $pager->getSlice($request->query->get('_start', 0), $request->query->get('_end', 20));

        return new JsonResponse($results, 200, array(
            'X-Total-Count' => $nbResults,
        ));
    }

    public function postListAction($objectType, Request $request)
    {
        try {
            $this->dbal->insert($objectType, $request->request->all());
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'errors' => array('detail' => $e->getMessage()),
            ), 400);
        }

        $id = (integer) $this->dbal->lastInsertId();

        return $this->getObjectResponse($objectType, $id, 201);
    }

    public function getObjectAction($objectId, $objectType)
    {
        return $this->getObjectResponse($objectType, $objectId);
    }

    public function putObjectAction($objectId, $objectType, Request $request)
    {
        $data = $request->request->all();
        $request->request->remove('id');

        $result = $this->dbal->update($objectType, $data, array('id' => $objectId));
        if (0 === $result) {
            return new Response('', 404);
        }

        return $this->getObjectResponse($objectType, $objectId);
    }

    public function deleteObjectAction($objectId, $objectType)
    {
        $result = $this->dbal->delete($objectType, array('id' => $objectId));
        if (0 === $result) {
            return new Response('', 404);
        }

        return new JsonResponse('', 204);
    }

    private function getObjectResponse($name, $id, $status = 200)
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $query = $queryBuilder
            ->select('*')
            ->from($name)
            ->where('id = '.$queryBuilder->createPositionalParameter($id))
        ;

        $result = $query->execute()->fetchObject();
        if (false === $result) {
            return new Response('', 404);
        }

        return new JsonResponse($result, $status);
    }
}
