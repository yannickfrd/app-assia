<?php

namespace App\Service\Event;

use App\Entity\Support\SupportGroup;
use App\Form\Model\Event\TaskSearch;
use App\Repository\Event\TaskRepository;
use App\Security\CurrentUserService;
use App\Service\Pagination;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;

class TaskPaginator
{
    private $pagination;
    private $taskRepo;
    private $currentUserService;

    public function __construct(Pagination $pagination, TaskRepository $taskRepo, CurrentUserService $currentUserService)
    {
        $this->pagination = $pagination;
        $this->taskRepo = $taskRepo;
        $this->currentUserService = $currentUserService;
    }

    /**
     * Donne les tâches du suivi.
     */
    public function paginate(Request $request, TaskSearch $search, ?SupportGroup $supportGroup = null)
    {
        // Si filtre ou tri utilisé, n'utilise pas le cache.
        if (null === $supportGroup || $request->query->count() > 0) {
            return $this->pagination->paginate(
                $this->taskRepo->findTasksQuery($search, $this->currentUserService, $supportGroup),
                $request
            );
        }

        // Sinon, récupère les tâches en cache.
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(
            SupportGroup::CACHE_SUPPORT_TASKS_KEY.$supportGroup->getId(),
            function (CacheItemInterface $item) use ($supportGroup, $search, $request) {
                $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

                return $this->pagination->paginate(
                    $this->taskRepo->findTasksQuery($search, $this->currentUserService, $supportGroup),
                    $request
                );
            }
        );
    }

    public function getTaskRepository(): TaskRepository
    {
        return $this->taskRepo;
    }
}
