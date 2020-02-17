<?php

namespace App\Controller;

use App\Entity\Referent;
use App\Entity\GroupPeople;
use App\Form\Model\ReferentSearch;
use App\Form\Referent\ReferentSearchType;
use App\Form\Referent\ReferentType;
use App\Repository\ReferentRepository;
use App\Repository\GroupPeopleRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReferentController extends AbstractController
{
    private $manager;
    private $repo;
    private $repoGroupPeople;

    public function __construct(EntityManagerInterface $manager, ReferentRepository $repo, GroupPeopleRepository $repoGroupPeople)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->repoGroupPeople = $repoGroupPeople;
    }

    /**
     * Liste des services référents du groupe de personnes
     * 
     * @Route("group/{id}/referents", name="referents")
     * @param ReferentSearch $referentSearch
     * @param Referent $referent
     * @param Request $request
     * @param Pagination $pagination
     * @return Response
     */
    public function listReferents($id, ReferentSearch $referentSearch = null, Request $request, Pagination $pagination): Response
    {
        $groupPeople = $this->repoGroupPeople->findGroupPeopleById($id);

        $referentSearch = new ReferentSearch;

        $formSearch = $this->createForm(ReferentSearchType::class, $referentSearch);
        $formSearch->handleRequest($request);

        $referents = $pagination->paginate($this->repo->findAllReferentsQuery($groupPeople->getId(), $referentSearch), $request);

        $form = $this->createForm(ReferentType::class, new Referent());

        return $this->render("app/referent/listReferents.html.twig", [
            "group_people" => $groupPeople,
            "form_search" => $formSearch->createView(),
            "form" => $form->createView(),
            "referents" => $referents
        ]);
    }

    /**
     * Nouveau service référent
     * 
     * @Route("group/{id}/referent/new", name="referent_new", methods="GET|POST")
     * @param int $id
     * @param Referent $referent
     * @param Request $request
     * @return Response
     */
    public function newReferent($id, Referent $referent = null, Request $request): Response
    {
        $groupPeople = $this->repoGroupPeople->findGroupPeopleById($id);

        $referent = new Referent();

        $form = $this->createForm(ReferentType::class, $referent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createReferent($groupPeople, $referent);
        }
        return $this->render("app/referent/referent.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un service référent
     * 
     * @Route("referent/{id}/edit", name="referent_edit", methods="GET|POST")
     * @param Referent $referent
     * @param Request $request
     * @return Response
     */
    public function editReferent(Referent $referent, Request $request): Response
    {
        $form = $this->createForm(ReferentType::class, $referent);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateReferent($referent);
        }
        return $this->render("app/referent/referent.html.twig", [
            "group_people" => $referent->getGroupPeople(),
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Supprime un service référent
     * 
     * @Route("referent/{id}/delete", name="referent_delete", methods="GET")
     * @param Referent $referent
     * @return Response
     */
    public function deleteReferent(Referent $referent): Response
    {
        $name = $referent->getName();

        $this->manager->remove($referent);
        $this->manager->flush();

        $this->addFlash("danger", "Le service social \"" . $name . "\" a été supprimé.");

        return $this->redirectToRoute("group_people_show", [
            "id" => $referent->getGroupPeople()->getId()
        ]);
    }

    /**
     * Crée un service référent une fois le formulaire soumis et validé
     *
     * @param GroupPeople $groupPeople
     * @param Referent $referent
     * @return Response
     */
    protected function createReferent(GroupPeople $groupPeople, Referent $referent): Response
    {
        $now = new \DateTime();

        $referent->setGroupPeople($groupPeople)
            ->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($referent);
        $this->manager->flush();

        $this->addFlash("success", "Le service social \"" . $referent->getName() . "\" a été créé.");

        return $this->redirectToRoute("referent_edit", [
            "id" => $referent->getId()
        ]);
    }

    /**
     * Met à jour le service référent une fois le formulaire soumis et validé
     *
     * @param Referent $referent
     * @return Response
     */
    protected function updateReferent(Referent $referent): Response
    {
        $referent->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash("success", "Le service social \"" . $referent->getName() . "\" a été mis à jour.");

        return $this->redirectToRoute("referent_edit", [
            "id" => $referent->getId()
        ]);
    }
}
