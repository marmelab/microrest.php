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
        // Prepare-prefixes
        $strongFilterKeyPrefix = 'strong_filter_';
        $searchOrKeyPrefix = 'search_or_';
        $searchAndKeyPrefix = 'search_and_';

        $queryBuilder = $this->dbal
            ->createQueryBuilder()
            ->from($objectType, 'o');

        $filterNames = array(
            '_strongFilter',
            '_strongFilterIn',
            '_searchOr',
            '_searchAnd',
        );

        // Fetching filters from request
        $filters = array_combine(
            $filterNames,
            array_map(
                function ($filter) use ($request) {
                    return $request->query->get($filter);
                },
                $filterNames
            )
        );

        // Throw expression when count of filters in request greater then one
        $count = array_reduce(
            $filters,
            function ($carry, $item) {
                return $carry + ($item !== null);
            },
            0
        );
        if ($count > 1) {
            return new JsonResponse(
                array(
                    'status'      => 'ERROR',
                    'status_code' => 400,
                    'message'     => 'You should use only one type of filters per request',
                ), 400
            );
        }

        // Return only assigned fields
        $fields = $request->query->get('_fields');
        $queryBuilder->select(
            $fields ? preg_replace('/([^,]+)/', 'o.$1', $fields) : 'o.*'
        );

        // Strong filter implementing
        // o.f1 = 'val1' AND o.f2 = 'val2' ...
        if ($filters['_strongFilter']) {
            $queryBuilder
                ->where(
                    implode(
                        ' and ',
                        array_map(
                            function ($item) use ($strongFilterKeyPrefix) {
                                return "{$item} = :{$strongFilterKeyPrefix}{$item}";
                            },
                            array_keys($filters['_strongFilter'])
                        )
                    )
                )
                ->setParameters(
                    array_combine(
                        array_map(
                            function ($key) use ($strongFilterKeyPrefix) {
                                return $strongFilterKeyPrefix . $key;
                            },
                            array_keys($filters['_strongFilter'])
                        ),
                        $filters['_strongFilter']
                    )
                );
        }

        // Searching with OR:
        // o.f1 LIKE '%val1%' OR o.f2 LIKE '%val2%'
        if ($filters['_searchOr']) {
            foreach ($filters['_searchOr'] as $key => $value) {
                $queryBuilder
                    ->orWhere(
                        $queryBuilder->expr()->like(
                            $key,
                            ":" . $searchOrKeyPrefix . $key
                        )
                    )
                    ->setParameter($searchOrKeyPrefix . $key, "%{$value}%");
            }
        }

        if ($filters['_strongFilterIn']) {
            foreach ($filters['_strongFilterIn'] as $key => $value) {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->in(
                            $key,
                            explode(',', $value)
                        )
                    );
            }
        }

        // Searching with AND:
        // o.f1 LIKE '%val1%' AND o.f2 LIKE '%val2%'
        if ($filters['_searchAnd']) {
            foreach ($filters['_searchAnd'] as $key => $value) {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->like(
                            $key,
                            ":" . $searchAndKeyPrefix . $key
                        )
                    )
                    ->setParameter($searchAndKeyPrefix . $key, "%{$value}%");
            }
        }

        if ($group = $request->query->get('_group')) {
            $queryBuilder->groupBy($group);
        }

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
