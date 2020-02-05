<?php

namespace App\Controller;

use App\Entity\SupportGroup;
use App\Entity\GroupPeopleAccommodation;
use App\Entity\PersonAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\GroupPeopleAccommodationRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Support\Accommodation\GroupPeopleAccommodationType;
use App\Repository\PersonAccommodationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GroupPeopleAccommodationController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(EntityManagerInterface $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    /**
     * Liste des hébergements du suivi social
     * 
     * @Route("support/{id}/accommodations", name="support_accommodations")
     * @param SupportGroupRepository $supportRepo
     * @return Response
     */
    public function viewSupportAccommodations($id, SupportGroupRepository $supportRepo, GroupPeopleAccommodationRepository $repo): Response
    {
        $supportGroup = $supportRepo->findSupportById($id);

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $groupPeopleAccommodations = $repo->findBy(["supportGroup" => $supportGroup]);

        return $this->render("app/support/listGroupPeopleAccommodations.html.twig", [
            "support" => $supportGroup,
            "support_group_accommodations" => $groupPeopleAccommodations,
        ]);
    }

    /**
     * Créer un nouvel hébergement 
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
     * Editer un hébergement 
     * 
     * @Route("/accommodation/{id}", name="support_accommodation_edit", methods="GET|POST")
     * @param GroupPeopleAccommodationRepository $repo
     * @param Request $request
     * @return Response
     */
    public function editGroupPeopleAccommodation($id, GroupPeopleAccommodationRepository $repo, Request $request): Response
    {
        $groupPeopleAccommodation = $repo->findOneById($id);

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
     * @param GroupPeopleAccommodationRepository $repo
     * @return Response
     */
    public function addPeopleInAccommodation($id, GroupPeopleAccommodationRepository $repo): Response
    {
        $groupPeopleAccommodation = $repo->findOneById($id);

        $people = [];

        foreach ($groupPeopleAccommodation->getPersonAccommodations() as $personAccommodations) {
            $people[] = $personAccommodations->getPerson()->getId();
        }

        foreach ($groupPeopleAccommodation->getSupportGroup()->getGroupPeople()->getrolePerson() as $rolePerson) {

            if (!in_array($rolePerson->getPerson()->getId(), $people)) {

                $personAccommodation = new PersonAccommodation();

                $user = $this->security->getUser();
                $now = new \DateTime();

                $personAccommodation->setGroupPeopleAccommodation($groupPeopleAccommodation)
                    ->setPerson($rolePerson->getPerson())
                    ->setAccommodation($groupPeopleAccommodation->getAccommodation())
                    ->setStartDate($groupPeopleAccommodation->getStartDate())
                    ->setEndDate($groupPeopleAccommodation->getEndDate())
                    ->setCreatedAt($now)
                    ->setCreatedBy($user)
                    ->setUpdatedAt($now)
                    ->setUpdatedBy($user);

                $this->manager->persist($personAccommodation);
            }
        }
        $this->manager->flush();

        $this->addFlash("success", "Les personnes ont été ajoutées à la prise en charge.");

        return $this->redirectToRoute("support_accommodation_edit", [
            "id" => $groupPeopleAccommodation->getId()
        ]);
    }


    /**
     * Supprime la prise en charge du groupe
     * 
     * @Route("support/group-people-accommodation/{id}/delete", name="support_group_people_accommodation_delete")
     * @param GroupPeopleAccommodationRepository $repo
     * @return Response
     */
    public function deleteGroupPeopleAccommodation(GroupPeopleAccommodation $groupPeopleAccommodation): Response
    {
        // $groupPeopleAccommodation = $repo->findOneById($id);

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

    protected function createGroupPeopleAccommodation($groupPeopleAccommodation)
    {
        $user = $this->security->getUser();
        $now = new \DateTime();

        $groupPeopleAccommodation->setGroupPeople($groupPeopleAccommodation->getSupportGroup()->getGroupPeople())
            ->setCreatedAt($now)
            ->setCreatedBy($user)
            ->setUpdatedAt($now)
            ->setUpdatedBy($user);

        $this->manager->persist($groupPeopleAccommodation);

        foreach ($groupPeopleAccommodation->getSupportGroup()->getSupportPerson() as $supportPerson) {
            $personAccommodation = new PersonAccommodation();

            $personAccommodation->setGroupPeopleAccommodation($groupPeopleAccommodation)
                ->setPerson($supportPerson->getPerson())
                ->setAccommodation($groupPeopleAccommodation->getAccommodation())
                ->setStartDate($groupPeopleAccommodation->getStartDate())
                ->setEndDate($groupPeopleAccommodation->getEndDate())
                ->setCreatedAt($now)
                ->setCreatedBy($user)
                ->setUpdatedAt($now)
                ->setUpdatedBy($user);

            $this->manager->persist($personAccommodation);
        };

        $this->manager->flush();

        $this->addFlash("success", "L'hébergement a été créé.");

        return $this->redirectToRoute("support_accommodations", [
            "id" => $groupPeopleAccommodation->getSupportGroup()->getId()
        ]);
    }

    protected function updateGroupPeopleAccommodation($groupPeopleAccommodation)
    {
        $user = $this->security->getUser();
        $now = new \DateTime();

        $groupPeopleAccommodation->setUpdatedAt($now)
            ->setUpdatedBy($user);

        foreach ($groupPeopleAccommodation->getPersonAccommodations() as $personAccommodation) {

            $personAccommodation->setAccommodation($groupPeopleAccommodation->getAccommodation())
                ->setUpdatedAt($now)
                ->setUpdatedBy($user);

            if ($personAccommodation->getEndDate() == null) {
                $personAccommodation->setEndDate($groupPeopleAccommodation->getEndDate());
            }

            if ($personAccommodation->getEndReason() == null) {
                $personAccommodation->setEndReason($groupPeopleAccommodation->getEndReason());
            }

            $this->manager->persist($personAccommodation);
        }
        $this->manager->flush();

        $this->addFlash("success", "L'hébergement a été modifié.");

        return $this->redirectToRoute("support_accommodation_edit", [
            "id" => $groupPeopleAccommodation->getId()
        ]);
    }
}
