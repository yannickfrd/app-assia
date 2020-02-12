<?php

namespace App\Controller;

use App\Entity\SupportGroup;
use App\Entity\AccommodationGroup;
use App\Entity\AccommodationPerson;
use App\Form\Support\Accommodation\AccommodationGroupType;
use App\Repository\AccommodationGroupRepository;
use App\Repository\AccommodationPersonRepository;
use App\Repository\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controller des hébergements des groupes de personnes
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
     * Liste des hébergements du suivi social
     * 
     * @Route("support/{id}/accommodations", name="support_accommodations")
     * @param int $id
     * @param SupportGroupRepository $supportRepo
     * @return Response
     */
    public function listSupportAccommodations($id, SupportGroupRepository $supportRepo): Response
    {
        $supportGroup = $supportRepo->findSupportById($id);

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $accommodationGroups = $this->repo->findBy(["supportGroup" => $supportGroup]);

        return $this->render("app/support/listAccommodationsGroup.html.twig", [
            "support" => $supportGroup,
            "support_group_accommodations" => $accommodationGroups,
        ]);
    }

    /**
     * Nouvel hébergement 
     * 
     * @Route("/support/{id}/accommodation/new", name="support_accommodation_new", methods="GET|POST")
     * @param SupportGroup $supportGroup
     * @param AccommodationGroup $accommodationGroup
     * @param Request $request
     * @return Response
     */
    public function newAccommodationGroup(SupportGroup $supportGroup, AccommodationGroup $accommodationGroup = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        // Vérifie si une prise en charge existe déjà pour le suivi
        if ($supportGroup->getAccommodationGroups()) {

            foreach ($supportGroup->getAccommodationGroups() as $accommodationGroup) {
                if ($accommodationGroup->getEndDate() == null) {

                    $this->addFlash("warning", "Attention, une prise en charge est déjà en cours.");

                    return $this->redirectToRoute("support_accommodation_edit", [
                        "id" => $accommodationGroup->getId()
                    ]);
                }
            }
        }

        if ($accommodationGroup == null) {
            $accommodationGroup = new AccommodationGroup();
            $accommodationGroup->setSupportGroup($supportGroup)
                ->setStartDate($supportGroup->getStartDate())
                ->setEndDate($supportGroup->getEndDate());
        }

        $form = $this->createForm(AccommodationGroupType::class, $accommodationGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->createAccommodationGroup($accommodationGroup);
        }
        return $this->render("app/support/accommodationGroup.html.twig", [
            "support" => $supportGroup,
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un hébergement 
     * 
     * @Route("/accommodation/{id}", name="support_accommodation_edit", methods="GET|POST")
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function editAccommodationGroup($id, Request $request, SupportGroupRepository $repoSupport): Response
    {
        $accommodationGroup = $this->repo->findOneById($id);
        $supportGroup = $repoSupport->findSupportById($accommodationGroup->getSupportGroup());

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $form = $this->createForm(AccommodationGroupType::class, $accommodationGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->updateAccommodationGroup($accommodationGroup);
        }
        return $this->render("app/support/accommodationGroup.html.twig", [
            "support" => $supportGroup,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Ajout de personnes à la prise en charge
     * 
     * @Route("/support/group_people_accommodation/{id}/add_people", name="support_group_people_accommodation_add_people", methods="GET|POST")
     * @param int $id
     * @return Response
     */
    public function addPeopleInAccommodation($id): Response
    {
        $accommodationGroup = $this->repo->findOneById($id);

        $this->denyAccessUnlessGranted("EDIT", $accommodationGroup->getSupportGroup());

        $this->createpPeopleAccommodation($accommodationGroup);

        $this->addFlash("success", "Les personnes ont été ajoutées à la prise en charge.");

        return $this->redirectToRoute("support_accommodation_edit", [
            "id" => $accommodationGroup->getId()
        ]);
    }

    /**
     * Supprime la prise en charge du groupe
     * 
     * @Route("support/group-people-accommodation/{id}/delete", name="support_group_people_accommodation_delete")
     * @param int $id
     * @return Response
     */
    public function deleteAccommodationGroup($id): Response
    {
        $accommodationGroup = $this->repo->findOneById($id);

        $supportGroup = $accommodationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $this->manager->remove($accommodationGroup);
        $this->manager->flush();

        $this->addFlash("danger", "La prise en charge a été supprimé.");

        return $this->redirectToRoute("support_accommodations", ["id" => $supportGroup->getId()]);
    }

    /**
     * Supprime la prise en charge d'une personne
     * 
     * @Route("support/person-accommodation/{id}/delete", name="support_person_accommodation_delete")
     * @param int $id
     * @param AccommodationPersonRepository $repo
     * @return Response
     */
    public function deleteAccommodationPerson($id, AccommodationPersonRepository $repo): Response
    {
        $accommodationPerson = $repo->findOneById($id);

        $this->denyAccessUnlessGranted("EDIT", $accommodationPerson->getAccommodationGroup()->getSupportGroup());

        $this->manager->remove($accommodationPerson);
        $this->manager->flush();

        $this->addFlash("danger", $accommodationPerson->getPerson()->getFullname() . " a été retiré de la prise en charge.");

        return $this->redirectToRoute("support_accommodation_edit", [
            "id" => $accommodationPerson->getAccommodationGroup()->getId()
        ]);
    }

    protected function createAccommodationGroup(AccommodationGroup $accommodationGroup)
    {
        $now = new \DateTime();

        $accommodationGroup->setGroupPeople($accommodationGroup->getSupportGroup()->getGroupPeople())
            ->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($accommodationGroup);

        $this->createpPeopleAccommodation($accommodationGroup);

        $this->manager->flush();

        $this->addFlash("success", "L'hébergement a été créé.");

        return $this->redirectToRoute("support_accommodations", [
            "id" => $accommodationGroup->getSupportGroup()->getId()
        ]);
    }

    protected function updateAccommodationGroup(AccommodationGroup $accommodationGroup)
    {
        $now = new \DateTime();

        $accommodationGroup->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        foreach ($accommodationGroup->getAccommodationPersons() as $accommodationPerson) {

            $accommodationPerson->setAccommodation($accommodationGroup->getAccommodation())
                ->setUpdatedAt($now)
                ->setUpdatedBy($this->getUser());

            if ($accommodationPerson->getEndDate() == null) {
                $accommodationPerson->setEndDate($accommodationGroup->getEndDate());
            }

            if ($accommodationPerson->getEndReason() == null) {
                $accommodationPerson->setEndReason($accommodationGroup->getEndReason());
            }
        }
        $this->manager->flush();

        $this->addFlash("success", "L'hébergement a été mis à jour.");

        return $this->redirectToRoute("support_accommodation_edit", [
            "id" => $accommodationGroup->getId()
        ]);
    }

    /**
     * Crée les prises en charge individuelles
     *
     * @param AccommodationGroup $accommodationGroup
     * @return array
     */
    protected function createpPeopleAccommodation(AccommodationGroup $accommodationGroup)
    {
        $people = [];

        foreach ($accommodationGroup->getAccommodationPersons() as $accommodationPersons) {
            $people[] = $accommodationPersons->getPerson()->getId();
        }

        foreach ($accommodationGroup->getSupportGroup()->getGroupPeople()->getrolePerson() as $rolePerson) {

            if (!in_array($rolePerson->getPerson()->getId(), $people)) {

                $accommodationPerson = new AccommodationPerson();
                $now = new \DateTime();

                $accommodationPerson->setAccommodationGroup($accommodationGroup)
                    ->setPerson($rolePerson->getPerson())
                    ->setAccommodation($accommodationGroup->getAccommodation())
                    ->setStartDate($accommodationGroup->getStartDate())
                    ->setEndDate($accommodationGroup->getEndDate())
                    ->setCreatedAt($now)
                    ->setCreatedBy($this->getUser())
                    ->setUpdatedAt($now)
                    ->setUpdatedBy($this->getUser());

                $this->manager->persist($accommodationPerson);
            }
        }
        $this->manager->flush();
    }
}
