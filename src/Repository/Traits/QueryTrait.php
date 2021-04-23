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
    protected function addOrganizationFilters(QueryBuilder $query, object $search): QueryBuilder
    {
        $query = $this->addPolesFilter($query, $search);
        $query = $this->addServicesFilter($query, $search);
        $query = $this->addSubServicesFilter($query, $search);
        $query = $this->addDevicesFilter($query, $search);
        $query = $this->addReferentsFilter($query, $search);

        return $query;
    }

    protected function addPolesFilter(QueryBuilder $query, object $search): QueryBuilder
    {
        if ($search->getPoles() && count($search->getPoles()) > 0) {
            $query = $this->addOrWhere($query, 's.pole', $search->getPoles());
        }

        return $query;
    }

    protected function addServicesFilter(QueryBuilder $query, object $search): QueryBuilder
    {
        if ($search->getServices() && count($search->getServices()) > 0) {
            $query = $this->addOrWhere($query, 'sg.service', $search->getServices());
        }

        return $query;
    }

    protected function addSubServicesFilter(QueryBuilder $query, object $search): QueryBuilder
    {
        if ($search->getSubServices() && count($search->getSubServices()) > 0) {
            $query = $this->addOrWhere($query, 'sg.subService', $search->getSubServices());
        }

        return $query;
    }

    protected function addDevicesFilter(QueryBuilder $query, object $search): QueryBuilder
    {
        if ($search->getDevices() && count($search->getDevices()) > 0) {
            $query = $this->addOrWhere($query, 'sg.device', $search->getDevices());
        }

        return $query;
    }

    protected function addReferentsFilter(QueryBuilder $query, object $search): QueryBuilder
    {
        if ($search->getReferents() && count($search->getReferents()) > 0) {
            $query = $this->addOrWhere($query, 'sg.referent', $search->getReferents());
        }

        return $query;
    }

    protected function filterByServiceType(QueryBuilder $query, string $dataClass = null): QueryBuilder
    {
        if (HotelSupportSearch::class === $dataClass) {
            $query->andWhere('s.type = :type')
                ->setParameter('type', Service::SERVICE_TYPE_HOTEL);
        }
        if (AvdlSupportSearch::class === $dataClass) {
            $query->andWhere('s.type = :type')
                ->setParameter('type', Service::SERVICE_TYPE_AVDL);
        }

        return $query;
    }

    /**
     * @param array|ArrayCollection $values
     */
    protected function addOrWhere(QueryBuilder $query, string $x, $values): QueryBuilder
    {
        $expr = $query->expr();
        $orX = $expr->orX();
        foreach ($values as $value) {
            $orX->add($expr->eq($x, $value));
            $query->andWhere($orX);
        }

        return $query;
    }
}
