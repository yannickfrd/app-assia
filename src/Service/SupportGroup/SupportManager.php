<?php

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Place\PlaceGroupManager;
use App\Service\SiSiao\SiSiaoEvaluationImporter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SupportManager
{
    use SupportPersonCreator;

    private $supportGroupRepo;
    private $em;
    private $supportDuplicator;
    private $siSiaoEvalImporter;
    private $supportChecker;
    private $flashBag;

    public function __construct(
        SupportGroupRepository $supportGroupRepo,
        EntityManagerInterface $em,
        SupportDuplicator $supportDuplicator,
        SiSiaoEvaluationImporter $siSiaoEvalImporter,
        SupportChecker $supportChecker,
        FlashBagInterface $flashBag
    ) {
        $this->supportDuplicator = $supportDuplicator;
        $this->em = $em;
        $this->siSiaoEvalImporter = $siSiaoEvalImporter;
        $this->supportChecker = $supportChecker;
        $this->flashBag = $flashBag;
        $this->supportGroupRepo = $supportGroupRepo;
    }

    /**
     * Donne un nouveau suivi paramétré.
     */
    public function getNewSupportGroup(PeopleGroup $peopleGroup, Request $request): SupportGroup
    {
        $supportGroup = (new SupportGroup())->setPeopleGroup($peopleGroup);

        /** @var array|null */
        $support = $request->request->get('support');
        $serviceId = null;

        if ($support) {
            $serviceId = $support['service'];
        }

        if ((int) $serviceId) {
            $service = $this->em->getRepository(Service::class)->find($serviceId);
            $supportGroup->setService($service);
        }

        return $supportGroup;
    }

    /**
     * Donne le suivi social complet.
     */
    public function getFullSupportGroup(int $id): ?SupportGroup
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        return $cache->get(SupportGroup::CACHE_FULLSUPPORT_KEY.$id, function (CacheItemInterface $item) use ($id) {
            $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

            return $this->supportGroupRepo->findFullSupportById($id);
        });
    }

    /**
     * Donne le suivi social.
     */
    public function getSupportGroup(int $id): ?SupportGroup
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        return $cache->get(SupportGroup::CACHE_SUPPORT_KEY.$id, function (CacheItemInterface $item) use ($id) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->supportGroupRepo->findSupportById($id);
        });
    }

    /**
     * Créé un nouveau suivi.
     */
    public function create(SupportGroup $supportGroup, ?Form $form): ?SupportGroup
    {
        // Vérifie si un suivi est déjà en cours pour ce ménage dans ce service.
        if (SupportGroup::STATUS_ENDED !== $supportGroup->getStatus() && $this->activeSupportExists($supportGroup)) {
            $this->flashBag->add('danger', 'Attention, un suivi social est déjà en cours pour '.(
                count($supportGroup->getPeopleGroup()->getPeople()) > 1 ? 'ce ménage.' : 'cette personne.'
            ));

            return null;
        }

        if ($supportGroup->getService()->getCoefficient()) {
            $supportGroup->setCoefficient($supportGroup->getDevice()->getCoefficient());
        }

        // Contrôle le service du suivi
        switch ($supportGroup->getService()->getType()) {
            case Service::SERVICE_TYPE_AVDL:
                $supportGroup = (new AvdlService())->updateSupportGroup($supportGroup);
                break;
            case Service::SERVICE_TYPE_HOTEL:
                $supportGroup = (new HotelSupportService())->updateSupportGroup($supportGroup);
                break;
        }

        $this->em->persist($supportGroup);

        // Créé un suivi social individuel pour chaque personne du groupe
        foreach ($supportGroup->getPeopleGroup()->getRolePeople() as $rolePerson) {
            $supportPerson = $this->createSupportPerson($supportGroup, $rolePerson);
            $this->em->persist($supportPerson);

            $supportGroup->addSupportPerson($supportPerson);
        }

        $this->flashBag->add('success', 'Le suivi social est créé.');

        if ($form && $form->has('_place') && $place = $form->get('_place')->getData()) {
            (new PlaceGroupManager($this->em, $this->flashBag))->createPlaceGroup($supportGroup, null, $place);
        }

        $supportGroup->setNbPeople($supportGroup->getSupportPeople()->count());

        $this->em->flush();

        $this->clone($supportGroup, $form);

        $this->deleteCacheItems($supportGroup);

        return $supportGroup;
    }

    public function clone(SupportGroup $supportGroup, Form $form)
    {
        if (null !== $form->get('_cloneSupport')->getData()) {
            $this->supportDuplicator->duplicate($supportGroup);
        }
        if (null !== $form->get('_siSiaoImport')->getData()) {
            $this->siSiaoEvalImporter->import($supportGroup);
        }
    }

    public function update(SupportGroup $supportGroup, ?User $currentReferent = null): void
    {
        $supportGroup->setUpdatedAt(new \DateTime());

        switch ($supportGroup->getService()->getType()) {
            case Service::SERVICE_TYPE_AVDL:
                $supportGroup = (new AvdlService())->updateSupportGroup($supportGroup);
                break;
            case Service::SERVICE_TYPE_HOTEL:
                $supportGroup = (new HotelSupportService())->updateSupportGroup($supportGroup);
                break;
        }

        $this->updateSupportPeople($supportGroup);
        $this->updatePlaceGroup($supportGroup);
        $this->updateNbPeople($supportGroup);
        $this->deleteCacheItems($supportGroup, $currentReferent);
        $this->supportChecker->checkValidHeader($supportGroup);

        $this->em->flush();
    }

    /**
     * Met à jour le nombre de personnes du suivi.
     */
    public function updateNbPeople(SupportGroup $supportGroup): void
    {
        $today = new \DateTime();
        $nbPeople = 0;
        $nbChildrenUnder3years = 0;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getEndDate() === $supportGroup->getEndDate()) {
                if (!$person = $supportPerson->getPerson()) {
                    continue;
                }

                $birthdate = $person->getBirthdate();
                $age = $birthdate->diff($supportPerson->getEndDate() ?? $today)->y ?? 0;
                if ($age < 3) {
                    ++$nbChildrenUnder3years;
                }
                ++$nbPeople;
            }
        }
        $supportGroup->setNbPeople($nbPeople);
        $supportGroup->setNbChildrenUnder3years($nbChildrenUnder3years);
    }

    /**
     * Vide le cache du suivi social et des indicateurs du service.
     */
    public static function deleteCacheItems(SupportGroup $supportGroup, ?User $currentReferent = null)
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        $supportGroupId = $supportGroup->getId();

        if ($supportGroup->getReferent()) {
            $cache->deleteItem(User::CACHE_USER_SUPPORTS_KEY.$supportGroup->getReferent()->getId());
        }

        if ($currentReferent && $currentReferent != $supportGroup->getReferent()) {
            $cache->deleteItem(User::CACHE_USER_SUPPORTS_KEY.$currentReferent->getId());
        }

        $cache->deleteItems([
            PeopleGroup::CACHE_GROUP_SUPPORTS_KEY.$supportGroup->getPeopleGroup()->getId(),
            SupportGroup::CACHE_SUPPORT_KEY.$supportGroupId,
            SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroupId,
            EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroupId,
            Service::CACHE_INDICATORS_KEY.$supportGroup->getService()->getId(),
        ]);
    }

    /**
     * Vérifie si un suivi social est déjà en cours dans le même service.
     */
    private function activeSupportExists(SupportGroup $supportGroup): ?SupportGroup
    {
        return $this->em->getRepository(SupportGroup::class)->findOneBy([
            'peopleGroup' => $supportGroup->getPeopleGroup(),
            'status' => SupportGroup::STATUS_IN_PROGRESS,
            'service' => $supportGroup->getService(),
        ]);
    }

    /**
     * Met à jour les suivis sociales individuelles des personnes.
     */
    private function updateSupportPeople(SupportGroup $supportGroup): void
    {
        $nbPeople = $supportGroup->getSupportPeople()->count();

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            // Si c'est une personne seule ou si la date de début de suivi est vide, copie la date de début de suivi.
            if (1 === $nbPeople || null === $supportPerson->getStartDate()) {
                $supportPerson->setStartDate($supportGroup->getStartDate());
            }
            if (1 === $nbPeople || null === $supportPerson->getEndDate() || null === $supportPerson->getEndStatus()) {
                $supportPerson
                    ->setStatus($supportGroup->getStatus())
                    ->setEndDate($supportGroup->getEndDate())
                    ->setEndReason($supportGroup->getEndReason())
                    ->setEndStatus($supportGroup->getEndStatus())
                    ->setEndStatusComment($supportGroup->getEndStatusComment());
            }
            if ($supportPerson->getEndDate()) {
                $supportPerson->setStatus(SupportGroup::STATUS_ENDED);

                if (null === $supportPerson->getStatus()) {
                    $supportPerson->setStatus($supportGroup->getStatus());
                }
                if (null === $supportPerson->getEndReason()) {
                    $supportPerson->setEndReason($supportGroup->getEndReason());
                }
                if (null === $supportPerson->getEndStatus()) {
                    $supportPerson->setEndStatus($supportGroup->getEndStatus());
                }
                if (null === $supportPerson->getEndStatusComment()) {
                    $supportPerson->setEndStatusComment($supportGroup->getEndStatusComment());
                }
            }

            // Vérifie si la date de suivi n'est pas antérieure à la date de naissance.
            $person = $supportPerson->getPerson();
            if ($supportPerson->getStartDate() && $person && $supportPerson->getStartDate() < $person->getBirthdate()) {
                // Si c'est le cas, on prend en compte la date de naissance
                $supportPerson->setStartDate($person->getBirthdate());
                // $this->addFlash('light', $supportPerson->getPerson()->getFullname().' : la date de début de suivi retenue est sa date de naissance.');
            }
        }
    }

    /**
     * Met à jour la prise en charge du groupe.
     */
    private function updatePlaceGroup(SupportGroup $supportGroup): void
    {
        // Si le statut du suivi est égal à terminé et si  "Fin d'hébergement" coché, alors met à jour la prise en charge
        foreach ($supportGroup->getPlaceGroups() as $placeGroup) {
            if (SupportGroup::STATUS_ENDED === $supportGroup->getStatus() && $supportGroup->getEndPlace() && !$placeGroup->getEndDate()) {
                null === $placeGroup->getEndDate() ? $placeGroup->setEndDate($supportGroup->getEndDate()) : null;
                null === $placeGroup->getEndReason() ? $placeGroup->setEndReason(PlaceGroup::END_REASON_SUPPORT_ENDED) : null;
            }
            $this->updatePlacePeople($placeGroup);
        }
    }

    /**
     * Met à jour la prise en charge des personnes du groupe.
     */
    private function updatePlacePeople(PlaceGroup $placeGroup): void
    {
        foreach ($placeGroup->getPlacePeople() as $placePerson) {
            if (null === $supportPerson = $placePerson->getSupportPerson()) {
                return;
            }

            $person = $supportPerson->getPerson();

            if (null === $placePerson->getEndDate()) {
                $placePerson->setEndDate($supportPerson->getEndDate());
            }
            if ($supportPerson->getEndDate() && null === $placePerson->getEndReason()) {
                $placePerson->setEndReason(PlaceGroup::END_REASON_SUPPORT_ENDED);
            }
            if ($supportPerson->getStartDate() && $supportPerson->getStartDate() < $person->getBirthdate()) {
                $placePerson->setStartDate($person->getBirthdate());
                $this->flashBag->add('warning', 'La date de début d\'hébergement ne peut pas être antérieure à la date de naissance de la personne ('.$person->getFullname().').');
            }
        }
    }
}
