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
        $qb = $this->addPolesFilter($qb, $search);
        $qb = $this->addServicesFilter($qb, $search);
        $qb = $this->addSubServicesFilter($qb, $search);
        $qb = $this->addDevicesFilter($qb, $search);
        $qb = $this->addReferentsFilter($qb, $search);

        return $qb;
    }

    protected function addPolesFilter(QueryBuilder $qb, object $search, string $x = 's.pole'): QueryBuilder
    {
        if ($search->getPoles() && count($search->getPoles()) > 0) {
            $qb = $this->addOrWhere($qb, $x, $search->getPoles());
        }

        return $qb;
    }

    protected function addServicesFilter(QueryBuilder $qb, object $search, string $x = 's.id'): QueryBuilder
    {
        if ($search->getServices() && count($search->getServices()) > 0) {
            $qb = $this->addOrWhere($qb, $x, $search->getServices());
        }

        return $qb;
    }

    protected function addSubServicesFilter(QueryBuilder $qb, object $search, string $x = 'sg.subService'): QueryBuilder
    {
        if ($search->getSubServices() && count($search->getSubServices()) > 0) {
            $qb = $this->addOrWhere($qb, $x, $search->getSubServices());
        }

        return $qb;
    }

    protected function addDevicesFilter(QueryBuilder $qb, object $search, string $x = 'sg.device'): QueryBuilder
    {
        if ($search->getDevices() && count($search->getDevices()) > 0) {
            $qb = $this->addOrWhere($qb, $x, $search->getDevices());
        }

        return $qb;
    }

    protected function addReferentsFilter(QueryBuilder $qb, object $search, string $x = 'sg.referent'): QueryBuilder
    {
        if ($search->getReferents() && count($search->getReferents()) > 0) {
            $qb = $this->addOrWhere($qb, $x, $search->getReferents());
        }

        return $qb;
    }

    protected function filterByServiceType(QueryBuilder $qb, string $dataClass = null): QueryBuilder
    {
        if (HotelSupportSearch::class === $dataClass) {
            $qb->andWhere('s.type = :type')
                ->setParameter('type', Service::SERVICE_TYPE_HOTEL);
        }
        if (AvdlSupportSearch::class === $dataClass) {
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
