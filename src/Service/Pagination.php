<?php

namespace App\Service;

use Doctrine\ORM\Query;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\Pagination\PaginationInterface;

class Pagination
{
    protected $paginator;

    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Pagination
     *
     * @param Query $query
     * @param int $limit
     * @return PaginationInterface
     */
    public function paginate(Query $query, Request $request, int $limit = 20): PaginationInterface
    {
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt("page", 1), // page number
            $limit // limit per page
        );
        $pagination->setCustomParameters([
            "align" => "right", // align pagination
        ]);

        return $pagination;
    }
}
