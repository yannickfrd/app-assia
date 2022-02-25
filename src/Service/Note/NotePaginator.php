<?php

namespace App\Service\Note;

use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\NoteSearch;
use App\Form\Model\Support\SupportNoteSearch;
use App\Repository\Support\NoteRepository;
use App\Security\CurrentUserService;
use App\Service\Pagination;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

class NotePaginator
{
    public const NB_ITEMS = 10;

    private $pagination;
    private $noteRepo;
    private $currentUser;

    public function __construct(Pagination $pagination, NoteRepository $noteRepo, CurrentUserService $currentUser)
    {
        $this->pagination = $pagination;
        $this->noteRepo = $noteRepo;
        $this->currentUser = $currentUser;
    }

    /**
     * Donne les rendez-vous du suivi.
     */
    public function paginateSupportNotes(SupportGroup $supportGroup, Request $request, SupportNoteSearch $search): object
    {
        // Si filtre ou tri utilisé, n'utilise pas le cache.
        if ($request->query->count() > 0 || $search->getNoteId()) {
            return $this->pagination->paginate(
                $this->noteRepo->findNotesOfSupportQuery($supportGroup->getId(), $search),
                $request,
                self::NB_ITEMS
            );
        }

        // Sinon, récupère les notes en cache.
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(
            SupportGroup::CACHE_SUPPORT_NOTES_KEY.$supportGroup->getId(),
            function (CacheItemInterface $item) use ($supportGroup, $search, $request) {
                $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

                return $this->pagination->paginate(
                    $this->noteRepo->findNotesOfSupportQuery($supportGroup->getId(), $search),
                    $request,
                    self::NB_ITEMS
                );
            }
        );
    }

    public function paginateNotes(Request $request, NoteSearch $search): object
    {
        return $this->pagination->paginate(
            $this->noteRepo->findNotesQuery($search, $this->currentUser),
            $request,
            self::NB_ITEMS
        );
    }
}
