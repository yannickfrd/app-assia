<?php

namespace App\EventDispatcher\People;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\SupportGroup;
use App\Event\People\PeopleGroupEvent;
use App\Service\People\PeopleGroupChecker;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PeopleGroupSubscriber implements EventSubscriberInterface
{
    private $peopleGroupChecker;

    public function __construct(PeopleGroupChecker $peopleGroupChecker)
    {
        $this->peopleGroupChecker = $peopleGroupChecker;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'people_group.before_update' => [
                ['checkValidHeader', 50],
                ['setNbPeople', 0],
            ],
            'people_group.after_update' => 'discache',
        ];
    }

    public function setNbPeople(PeopleGroupEvent $event): void
    {
        $peopleGroup = $event->getPeopleGroup();
        $nbPeople = count($peopleGroup->getRolePeople());
        $peopleGroup->setNbPeople($nbPeople);
    }

    public function checkValidHeader(PeopleGroupEvent $event): void
    {
        $this->peopleGroupChecker->checkValidHeader($event->getPeopleGroup());
    }

    /**
     * Supprime le cache.
     */
    public function discache(PeopleGroupEvent $event): void
    {
        $supports = $event->getSupports();
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if ($supports) {
            foreach ($supports as $supportGroup) {
                $cache->deleteItems([
                    SupportGroup::CACHE_SUPPORT_KEY.$supportGroup->getId(),
                    SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId(),
                    EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(),
                ]);
            }
        }
    }
}
