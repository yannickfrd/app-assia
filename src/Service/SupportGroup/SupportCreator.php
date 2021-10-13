<?php

namespace App\Service\SupportGroup;

use App\Entity\Organization\Service;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use App\Event\Support\SupportGroupEvent;
use App\Service\Place\PlaceGroupManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SupportCreator
{
    use SupportPersonCreator;

    private $em;
    private $dispatcher;
    private $flashBag;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $dispatcher, FlashBagInterface $flashBag)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->flashBag = $flashBag;
    }

    /**
     * Donne un nouveau suivi paramétré.
     */
    public function getNewSupportGroup(PeopleGroup $peopleGroup, Request $request): SupportGroup
    {
        $supportGroup = (new SupportGroup())->setPeopleGroup($peopleGroup);

        $serviceId = $request->request->get('support')['service'];

        if ((int) $serviceId) {
            $service = $this->em->getRepository(Service::class)->find($serviceId);
            $supportGroup->setService($service);
        }

        return $supportGroup;
    }

    /**
     * Créé un nouveau suivi.
     */
    public function create(SupportGroup $supportGroup, ?Form $form): ?SupportGroup
    {
        // Vérfie si un suivi est déjà en cours pour ce ménage dans ce service.
        if ($this->activeSupportExists($supportGroup)) {
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

        if ($form && $form->has('place') && $form->get('place')->getData()) {
            (new PlaceGroupManager($this->em, $this->flashBag))->createPlaceGroup($supportGroup, null, $form->get('place')->getData());
        }

        $this->dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.before_create');

        $this->em->flush();

        $this->dispatcher->dispatch(new SupportGroupEvent($supportGroup, $form), 'support.after_create');

        return $supportGroup;
    }

    /**
     * Vérifie si un suivi social est déjà en cours dans le même service.
     */
    protected function activeSupportExists(SupportGroup $supportGroup): ?SupportGroup
    {
        return $this->em->getRepository(SupportGroup::class)->findOneBy([
            'peopleGroup' => $supportGroup->getPeopleGroup(),
            'status' => SupportGroup::STATUS_IN_PROGRESS,
            'service' => $supportGroup->getService(),
        ]);
    }
}
