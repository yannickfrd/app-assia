<?php

namespace App\Repository\Traits;

use App\Entity\Organization\Service;
use App\Form\Model\Support\AvdlSupportSearch;
use App\Form\Model\Support\HotelSupportSearch;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;

trait QueryTrait
{
    /**
     * Add service, sub-service, device and referent filters.
     */
    protected function addOrganizationFilters(QueryBuilder $qb, object $search): QueryBuilder
    {
        $this->addPolesFilter($qb, $search);
        $this->addServicesFilter($qb, $search);
        $this->addSubServicesFilter($qb, $search);
        $this->addDevicesFilter($qb, $search);
        $this->addReferentsFilter($qb, $search);

        return $qb;
    }

    protected function addPolesFilter(QueryBuilder $qb, object $search, string $x = 's.pole'): QueryBuilder
    {
        if ($search->getPoles() && count($search->getPoles()) > 0) {
            $qb->andWhere($x.' IN (:poles)')
                ->setParameter('poles', $search->getPoles());
        }

        return $qb;
    }

    protected function addServicesFilter(QueryBuilder $qb, object $search, string $x = 's.id'): QueryBuilder
    {
        if ($search->getServices() && count($search->getServices()) > 0) {
            $qb->andWhere($x.' IN (:services)')
                ->setParameter('services', $search->getServices());
        }

        return $qb;
    }

    protected function addSubServicesFilter(QueryBuilder $qb, object $search, string $x = 'sg.subService'): QueryBuilder
    {
        if ($search->getSubServices() && count($search->getSubServices()) > 0) {
            $qb->andWhere($x.' IN (:subServices)')
                ->setParameter('subServices', $search->getSubServices());
        }

        return $qb;
    }

    protected function addDevicesFilter(QueryBuilder $qb, object $search, string $x = 'sg.device'): QueryBuilder
    {
        if ($search->getDevices() && count($search->getDevices()) > 0) {
            $qb->andWhere($x.' IN (:devices)')
                ->setParameter('devices', $search->getDevices());
        }

        return $qb;
    }

    protected function addReferentsFilter(QueryBuilder $qb, object $search, string $x = 'sg.referent'): QueryBuilder
    {
        if ($search->getReferents() && count($search->getReferents()) > 0) {
            $qb->andWhere($x.' IN (:referents)')
                ->setParameter('referents', $search->getReferents());
        }

        return $qb;
    }

    protected function addTagsFilter(QueryBuilder $qb, object $search, string $join): QueryBuilder
    {
        if ($search->getTags() && count($search->getTags()) > 0) {
            $qb->leftJoin($join, 't2')
                ->andWhere('t2.id IN (:tags)')
                ->setParameter('tags', $search->getTags());
        }

        return $qb;
    }

    protected function filterByServiceType(QueryBuilder $qb, string $className = null): QueryBuilder
    {
        if (HotelSupportSearch::class === $className) {
            $qb->andWhere('s.type = :type')
                ->setParameter('type', Service::SERVICE_TYPE_HOTEL);
        }
        if (AvdlSupportSearch::class === $className) {
            $qb->andWhere('s.type = :type')
                ->setParameter('type', Service::SERVICE_TYPE_AVDL);
        }

        return $qb;
    }

    /**
     * @param array|ArrayCollection $values
     */
    protected function addOrWhere(QueryBuilder $qb, string $x, $values): QueryBuilder
    {
        $expr = $qb->expr();
        $orX = $expr->orX();
        foreach ($values as $value) {
            $orX->add($expr->eq($x, $value));
            $qb->andWhere($orX);
        }

        return $qb;
    }
}
