<?php

namespace App\Controller;

use App\Entity\SupportGroup;
use App\Entity\GroupPeopleAccommodation;
use App\Entity\PersonAccommodation;
use App\Form\Support\Accommodation\GroupPeopleAccommodationType;
use App\Repository\GroupPeopleAccommodationRepository;
use App\Repository\PersonAccommodationRepository;
use App\Repository\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controller des hébergements des groupes de personnes
 */
class GroupPeopleAccommodationController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, GroupPeopleAccommodationRepository $repo)
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

        $groupPeopleAccommodations = $this->repo->findBy(["supportGroup" => $supportGroup]);

        return $this->render("app/support/listGroupPeopleAccommodations.html.twig", [
            "support" => $supportGroup,
            "support_group_accommodations" => $groupPeopleAccommodations,
        ]);
    }

    /**
     * Nouvel hébergement 
     * 
     * @Route("/support/{id}/accommodation/new", name="support_accommodation_new", methods="GET|POST")
     * @param SupportGroup $supportGroup
     * @param GroupPeopleAccommodation $groupPeopleAccommodation
     * @param Request $request
     * @return Response
     */
    public function newGroupPeopleAccommodation(SupportGroup $supportGroup, GroupPeopleAccommodation $groupPeopleAccommodation = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        // Vérifie si une prise en charge existe déjà pour le suivi
        if ($supportGroup->getGroupPeopleAccommodations()) {

            foreach ($supportGroup->getGroupPeopleAccommodations() as $groupPeopleAccommodation) {
                if ($groupPeopleAccommodation->getEndDate() == null) {

                    $this->addFlash("warning", "Attention, une prise en charge est déjà en cours.");

                    return $this->redirectToRoute("support_accommodation_edit", [
                        "id" => $groupPeopleAccommodation->getId()
                    ]);
                }
            }
        }

        if ($groupPeopleAccommodation == null) {
            $groupPeopleAccommodation = new GroupPeopleAccommodation();
            $groupPeopleAccommodation->setSupportGroup($supportGroup)
                ->setStartDate($supportGroup->getStartDate())
                ->setEndDate($supportGroup->getEndDate());
        }

        $form = $this->createForm(GroupPeopleAccommodationType::class, $groupPeopleAccommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->createGroupPeopleAccommodation($groupPeopleAccommodation);
        }
        return $this->render("app/support/groupPeopleAccommodation.html.twig", [
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
    public function editGroupPeopleAccommodation($id, Request $request): Response
    {
        $groupPeopleAccommodation = $this->repo->findOneById($id);

        $supportGroup = $groupPeopleAccommodation->getSupportGroup();

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $form = $this->createForm(GroupPeopleAccommodationType::class, $groupPeopleAccommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->updateGroupPeopleAccommodation($groupPeopleAccommodation);
        }
        return $this->render("app/support/groupPeopleAccommodation.html.twig", [
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
        $groupPeopleAccommodation = $this->repo->findOneById($id);

        $this->createpPeopleAccommodation($groupPeopleAccommodation);

        $this->addFlash("success", "Les personnes ont été ajoutées à la prise en charge.");

        return $this->redirectToRoute("support_accommodation_edit", [
            "id" => $groupPeopleAccommodation->getId()
        ]);
    }

    /**
     * Supprime la prise en charge du groupe
     * 
     * @Route("support/group-people-accommodation/{id}/delete", name="support_group_people_accommodation_delete")
     * @param int $id
     * @return Response
     */
    public function deleteGroupPeopleAccommodation($id): Response
    {
        $groupPeopleAccommodation = $this->repo->findOneById($id);

        $supportGroup = $groupPeopleAccommodation->getSupportGroup();

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $this->manager->remove($groupPeopleAccommodation);
        $this->manager->flush();

        $this->addFlash("danger", "La prise en charge a été supprimé.");

        return $this->redirectToRoute("support_accommodations", ["id" => $supportGroup->getId()]);
    }

    /**
     * Supprime la prise en charge d'une personne
     * 
     * @Route("support/person-accommodation/{id}/delete", name="support_person_accommodation_delete")
     * @param int $id
     * @param PersonAccommodationRepository $repo
     * @return Response
     */
    public function deletePersonAccommodation($id, PersonAccommodationRepository $repo): Response
    {
        $personAccommodation = $repo->findOneById($id);

        $this->denyAccessUnlessGranted("EDIT", $personAccommodation->getGroupPeopleAccommodation()->getSupportGroup());

        $this->manager->remove($personAccommodation);
        $this->manager->flush();

        $this->addFlash("danger", $personAccommodation->getPerson()->getFullname() . " a été retiré de la prise en charge.");

        return $this->redirectToRoute("support_accommodation_edit", [
            "id" => $personAccommodation->getGroupPeopleAccommodation()->getId()
        ]);
    }

    protected function createGroupPeopleAccommodation(GroupPeopleAccommodation $groupPeopleAccommodation)
    {
        $now = new \DateTime();

        $groupPeopleAccommodation->setGroupPeople($groupPeopleAccommodation->getSupportGroup()->getGroupPeople())
            ->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($groupPeopleAccommodation);

        $this->createpPeopleAccommodation($groupPeopleAccommodation);

        $this->manager->flush();

        $this->addFlash("success", "L'hébergement a été créé.");

        return $this->redirectToRoute("support_accommodations", [
            "id" => $groupPeopleAccommodation->getSupportGroup()->getId()
        ]);
    }

    protected function updateGroupPeopleAccommodation(GroupPeopleAccommodation $groupPeopleAccommodation)
    {
        $now = new \DateTime();

        $groupPeopleAccommodation->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        foreach ($groupPeopleAccommodation->getPersonAccommodations() as $personAccommodation) {

            $personAccommodation->setAccommodation($groupPeopleAccommodation->getAccommodation())
                ->setUpdatedAt($now)
                ->setUpdatedBy($this->getUser());

            if ($personAccommodation->getEndDate() == null) {
                $personAccommodation->setEndDate($groupPeopleAccommodation->getEndDate());
            }

            if ($personAccommodation->getEndReason() == null) {
                $personAccommodation->setEndReason($groupPeopleAccommodation->getEndReason());
            }
        }
        $this->manager->flush();

        $this->addFlash("success", "L'hébergement a été mis à jour.");

        return $this->redirectToRoute("support_accommodation_edit", [
            "id" => $groupPeopleAccommodation->getId()
        ]);
    }

    /**
     * Crée les prises en charge individuelles
     *
     * @param GroupPeopleAccommodation $groupPeopleAccommodation
     * @return array
     */
    protected function createpPeopleAccommodation(GroupPeopleAccommodation $groupPeopleAccommodation)
    {
        $people = [];

        foreach ($groupPeopleAccommodation->getPersonAccommodations() as $personAccommodations) {
            $people[] = $personAccommodations->getPerson()->getId();
        }

        foreach ($groupPeopleAccommodation->getSupportGroup()->getGroupPeople()->getrolePerson() as $rolePerson) {

            if (!in_array($rolePerson->getPerson()->getId(), $people)) {

                $personAccommodation = new PersonAccommodation();
                $now = new \DateTime();

                $personAccommodation->setGroupPeopleAccommodation($groupPeopleAccommodation)
                    ->setPerson($rolePerson->getPerson())
                    ->setAccommodation($groupPeopleAccommodation->getAccommodation())
                    ->setStartDate($groupPeopleAccommodation->getStartDate())
                    ->setEndDate($groupPeopleAccommodation->getEndDate())
                    ->setCreatedAt($now)
                    ->setCreatedBy($this->getUser())
                    ->setUpdatedAt($now)
                    ->setUpdatedBy($this->getUser());

                $this->manager->persist($personAccommodation);
            }
        }
        $this->manager->flush();
    }
}
