<?php

namespace App\Service\Event;

use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Event\EventSearch;
use App\Repository\Event\RdvRepository;
use App\Service\Pagination;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class RdvPaginator
{
    private $rdvRepo;
    private $pagination;

    /** @var User */
    private $user;

    public function __construct(RdvRepository $rdvRepo, Pagination $pagination, Security $security)
    {
        $this->rdvRepo = $rdvRepo;
        $this->pagination = $pagination;
        $this->user = $security->getUser();
    }

    /**
     * Donne les rendez-vous du suivi.
     */
    public function paginate(Request $request, EventSearch $search, ?SupportGroup $supportGroup = null): PaginationInterface
    {
        // Si filtre ou tri utilisé, n'utilise pas le cache.
        if (null === $supportGroup) {
            return $this->pagination->paginate(
                $this->rdvRepo->findRdvsQuery($search, $this->user),
                $request
            );
        }

        if ($request->query->count() > 0) {
            return $this->pagination->paginate(
                $this->rdvRepo->findRdvsQueryOfSupport($search, $supportGroup),
                $request
            );
        }

        // Sinon, récupère les rendez-vous en cache.
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(
            SupportGroup::CACHE_SUPPORT_RDVS_KEY.$supportGroup->getId(),
            function (CacheItemInterface $item) use ($supportGroup, $search, $request) {
                $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

                return $this->pagination->paginate($this->rdvRepo->findRdvsQueryOfSupport($search, $supportGroup), $request);
            }
        );
    }

    public function getRdvRepository(): RdvRepository
    {
        return $this->rdvRepo;
    }
}
