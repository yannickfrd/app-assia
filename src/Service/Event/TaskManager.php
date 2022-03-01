<?php

namespace App\Service\Event;

use App\Entity\Event\Task;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class TaskManager
{
    public function deleteCacheItems(Task $task): void
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        foreach ($task->getUsers() as $user) {
            $cache->deleteItems([
                User::CACHE_USER_TASKS_KEY.$user->getId(),
                User::CACHE_USER_SUPPORTS_KEY.$user->getId(),
            ]);
        }

        if ($supportGroup = $task->getSupportGroup()) {
            $cache->deleteItems([
                SupportGroup::CACHE_SUPPORT_TASKS_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_SUPPORT_NB_TASKS_KEY.$supportGroup->getId(),
            ]);
        }
    }
}
