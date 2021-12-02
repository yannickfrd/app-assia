<?php

namespace App\Service\Rdv;

use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportRdvSearch;
use App\Repository\Support\RdvRepository;
use App\Service\Pagination;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

class RdvPaginator
{
    private $rdvRepo;
    private $pagination;

    public function __construct(RdvRepository $rdvRepo, Pagination $pagination)
    {
        $this->rdvRepo = $rdvRepo;
        $this->pagination = $pagination;
    }

    /**
     * Donne les rendez-vous du suivi.
     */
    public function getRdvs(SupportGroup $supportGroup, Request $request, SupportRdvSearch $search): object
    {
        // Si filtre ou tri utilisé, n'utilise pas le cache.
        if ($request->query->count() > 0) {
            return $this->pagination->paginate($this->rdvRepo->findRdvsQueryOfSupport($supportGroup->getId(), $search), $request);
        }

        // Sinon, récupère les rendez-vous en cache.
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(
            SupportGroup::CACHE_SUPPORT_RDVS_KEY.$supportGroup->getId(),
            function (CacheItemInterface $item) use ($supportGroup, $search, $request) {
                $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

                return $this->pagination->paginate($this->rdvRepo->findRdvsQueryOfSupport($supportGroup->getId(), $search), $request);
            }
        );
    }
}
