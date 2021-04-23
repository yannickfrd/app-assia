<?php

namespace App\Service\SupportGroup;

use App\Entity\Organization\Service;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class SupportCreator
{
    use SupportPersonCreator;

    private $manager;
    private $supportGroupRepo;
    private $serviceRepo;

    public function __construct(
        EntityManagerInterface $manager,
        SupportGroupRepository $supportGroupRepo,
        ServiceRepository $serviceRepo)
    {
        $this->manager = $manager;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->serviceRepo = $serviceRepo;
    }

    /**
     * Donne un nouveau suivi paramétré.
     */
    public function getNewSupportGroup(PeopleGroup $peopleGroup, Request $request): SupportGroup
    {
        $supportGroup = (new SupportGroup())->setPeopleGroup($peopleGroup);

        $serviceId = $request->request->get('support')['service'];

        if ((int) $serviceId) {
            $supportGroup->setService($this->serviceRepo->find($serviceId));
        }

        return $supportGroup;
    }

    /**
     * Créé un nouveau suivi.
     */
    public function create(SupportGroup $supportGroup): ?SupportGroup
    {
        if ($this->activeSupportExists($supportGroup)) {
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

        $this->manager->persist($supportGroup);

        // Créé un suivi social individuel pour chaque personne du groupe
        foreach ($supportGroup->getPeopleGroup()->getRolePeople() as $rolePerson) {
            $supportPerson = $this->createSupportPerson($supportGroup, $rolePerson);
            $this->manager->persist($supportPerson);

            $supportGroup->addSupportPerson($supportPerson);
        }

        return $supportGroup;
    }

    /**
     * Vérifie si un suivi social est déjà en cours dans le même service.
     */
    protected function activeSupportExists(SupportGroup $supportGroup): ?SupportGroup
    {
        return $this->supportGroupRepo->findOneBy([
            'peopleGroup' => $supportGroup->getPeopleGroup(),
            'status' => SupportGroup::STATUS_IN_PROGRESS,
            'service' => $supportGroup->getService(),
        ]);
    }
}
