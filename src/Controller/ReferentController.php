<?php

namespace App\Controller;

use App\Entity\GroupPeople;
use App\Entity\Referent;
use App\Form\Referent\ReferentType;
use App\Repository\GroupPeopleRepository;
use App\Repository\ReferentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * Nouveau service référent.
     *
     * @Route("group/{id}/referent/new", name="referent_new", methods="GET|POST")
     *
     * @param int $id //GroupPeople
     */
    public function newReferent(int $id, Referent $referent = null, Request $request): Response
    {
        $groupPeople = $this->repoGroupPeople->findGroupPeopleById($id);

        $referent = new Referent();

        $form = ($this->createForm(ReferentType::class, $referent))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createReferent($groupPeople, $referent);
        }

        return $this->render('app/referent/referent.html.twig', [
            'group_people' => $groupPeople,
            'form' => $form->createView(),
            'edit_mode' => false,
        ]);
    }

    /**
     * Modification d'un service référent.
     *
     * @Route("referent/{id}/edit", name="referent_edit", methods="GET|POST")
     */
    public function editReferent(Referent $referent, Request $request): Response
    {
        $form = ($this->createForm(ReferentType::class, $referent))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Le service social "'.$referent->getName().'" a été mis à jour.');

            return $this->redirectToRoute('referent_edit', ['id' => $referent->getId()]);
        }

        return $this->render('app/referent/referent.html.twig', [
            'group_people' => $referent->getGroupPeople(),
            'form' => $form->createView(),
            'edit_mode' => true,
        ]);
    }

    /**
     * Supprime un service référent.
     *
     * @Route("referent/{id}/delete", name="referent_delete", methods="GET")
     */
    public function deleteReferent(Referent $referent): Response
    {
        $name = $referent->getName();

        $this->manager->remove($referent);
        $this->manager->flush();

        $this->addFlash('warning', 'Le service social "'.$name.'" a été supprimé.');

        return $this->redirectToRoute('group_people_show', [
            'id' => $referent->getGroupPeople()->getId(),
        ]);
    }

    /**
     * Crée un service référent une fois le formulaire soumis et validé.
     */
    protected function createReferent(GroupPeople $groupPeople, Referent $referent): Response
    {
        $referent->setGroupPeople($groupPeople);

        $this->manager->persist($referent);
        $this->manager->flush();

        $this->addFlash('success', 'Le service social "'.$referent->getName().'" a été créé.');

        return $this->redirectToRoute('referent_edit', [
            'id' => $referent->getId(),
        ]);
    }
}
