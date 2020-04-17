<?php

namespace App\Controller;

use App\Entity\AccommodationGroup;
use App\Entity\AccommodationPerson;
use App\Entity\SupportGroup;
use App\Form\Accommodation\AccommodationGroupType;
use App\Repository\AccommodationGroupRepository;
use App\Repository\AccommodationPersonRepository;
use App\Repository\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller des hébergements des groupes de personnes.
 */
class AccommodationGroupController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, AccommodationGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des hébergements du suivi social.
     *
     * @Route("support/{id}/accommodations", name="support_accommodations", methods="GET")
     */
    public function listSupportAccommodations(int $id, SupportGroupRepository $supportRepo): Response
    {
        $supportGroup = $supportRepo->findSupportById($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $accommodationGroups = $this->repo->findBy(['supportGroup' => $supportGroup]);

        return $this->render('app/accommodation/listAccommodationsGroup.html.twig', [
            'support' => $supportGroup,
            'support_group_accommodations' => $accommodationGroups,
        ]);
    }

    /**
     * Nouvel hébergement.
     *
     * @Route("/support/{id}/accommodation/new", name="support_accommodation_new", methods="GET|POST")
     */
    public function newAccommodationGroup(SupportGroup $supportGroup, AccommodationGroup $accommodationGroup = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // Vérifie si une prise en charge existe déjà pour le suivi
        if ($supportGroup->getAccommodationGroups()) {
            foreach ($supportGroup->getAccommodationGroups() as $accommodationGroup) {
                if (null == $accommodationGroup->getEndDate()) {
                    $this->addFlash('warning', 'Attention, une autre prise en charge est déjà en cours pour ce suivi.');
                }
            }
        }

        $accommodationGroup = (new AccommodationGroup())
            ->setSupportGroup($supportGroup)
            ->setStartDate($supportGroup->getStartDate())
            ->setEndDate($supportGroup->getEndDate());

        $form = ($this->createForm(AccommodationGroupType::class, $accommodationGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createAccommodationGroup($accommodationGroup);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Une erreur s'est produite");
        }

        return $this->render('app/accommodation/accommodationGroup.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un hébergement.
     *
     * @Route("/support/accommodation_group/{id}", name="support_accommodation_edit", methods="GET|POST")
     *
     * @param int $id // AccommodationGroup
     */
    public function editAccommodationGroup(int $id, Request $request, SupportGroupRepository $repoSupport): Response
    {
        $accommodationGroup = $this->repo->findOneById($id);
        $supportGroup = $repoSupport->findSupportById($accommodationGroup->getSupportGroup()->getId());

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm(AccommodationGroupType::class, $accommodationGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateAccommodationGroup($accommodationGroup);
        }

        return $this->render('app/accommodation/accommodationGroup.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Ajout de personnes à la prise en charge.
     *
     * @Route("/support/group_people_accommodation/{id}/add_people", name="support_group_people_accommodation_add_people", methods="GET")
     *
     * @param int $id // AccommodationGroup
     */
    public function addPeopleInAccommodation(int $id): Response
    {
        $accommodationGroup = $this->repo->findOneById($id);

        $this->denyAccessUnlessGranted('EDIT', $accommodationGroup->getSupportGroup());

        $this->createpPeopleAccommodation($accommodationGroup);

        $this->addFlash('success', 'Les personnes ont été ajoutées à la prise en charge.');

        return $this->redirectToRoute('support_accommodation_edit', [
            'id' => $accommodationGroup->getId(),
        ]);
    }

    /**
     * Supprime la prise en charge du groupe.
     *
     * @Route("support/group-people-accommodation/{id}/delete", name="support_group_people_accommodation_delete", methods="GET")
     */
    public function deleteAccommodationGroup(AccommodationGroup $accommodationGroup): Response
    {
        $supportGroup = $accommodationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('DELETE', $supportGroup);

        $this->manager->remove($accommodationGroup);
        $this->manager->flush();

        $this->addFlash('warning', 'La prise en charge a été supprimé.');

        return $this->redirectToRoute('support_accommodations', ['id' => $supportGroup->getId()]);
    }

    /**
     * Supprime la prise en charge d'une personne.
     *
     * @Route("support/person-accommodation/{id}/delete", name="support_person_accommodation_delete", methods="GET")
     */
    public function deleteAccommodationPerson(AccommodationPerson $accommodationPerson, AccommodationPersonRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $accommodationPerson->getAccommodationGroup()->getSupportGroup());

        $this->manager->remove($accommodationPerson);
        $this->manager->flush();

        $this->addFlash('warning', $accommodationPerson->getPerson()->getFullname().' a été retiré de la prise en charge.');

        return $this->redirectToRoute('support_accommodation_edit', [
            'id' => $accommodationPerson->getAccommodationGroup()->getId(),
        ]);
    }

    /**
     * Crée la prise en charge du groupe.
     */
    protected function createAccommodationGroup(AccommodationGroup $accommodationGroup): Response
    {
        $accommodationGroup->setGroupPeople($accommodationGroup->getSupportGroup()->getGroupPeople());

        $this->manager->persist($accommodationGroup);

        $this->createpPeopleAccommodation($accommodationGroup);

        $this->manager->flush();

        $this->addFlash('success', "L'hébergement a été créé.");

        return $this->redirectToRoute('support_accommodations', [
            'id' => $accommodationGroup->getSupportGroup()->getId(),
        ]);
    }

    /**
     * Met à jour la prise en charge du groupe.
     */
    protected function updateAccommodationGroup(AccommodationGroup $accommodationGroup): Response
    {
        foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
            if (null == $accommodationPerson->getEndDate()) {
                $accommodationPerson->setEndDate($accommodationGroup->getEndDate());
            }

            if (null == $accommodationPerson->getEndReason()) {
                $accommodationPerson->setEndReason($accommodationGroup->getEndReason());
            }
        }
        $this->manager->flush();

        $this->addFlash('success', "L'hébergement a été mis à jour.");

        return $this->redirectToRoute('support_accommodation_edit', [
            'id' => $accommodationGroup->getId(),
        ]);
    }

    /**
     * Crée les prises en charge individuelles.
     */
    protected function createpPeopleAccommodation(AccommodationGroup $accommodationGroup): void
    {
        $people = [];

        foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
            $people[] = $accommodationPerson->getPerson()->getId();
        }

        foreach ($accommodationGroup->getSupportGroup()->getGroupPeople()->getrolePerson() as $rolePerson) {
            if (!in_array($rolePerson->getPerson()->getId(), $people)) {
                $accommodationPerson = (new AccommodationPerson())
                    ->setAccommodationGroup($accommodationGroup)
                    ->setPerson($rolePerson->getPerson())
                    ->setStartDate($accommodationGroup->getStartDate())
                    ->setEndDate($accommodationGroup->getEndDate());

                $this->manager->persist($accommodationPerson);
            }
        }
        $this->manager->flush();
    }
}
