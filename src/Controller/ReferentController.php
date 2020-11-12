<?php

namespace App\Controller;

use App\Entity\GroupPeople;
use App\Entity\Referent;
use App\Entity\SupportGroup;
use App\Form\Referent\ReferentType;
use App\Repository\GroupPeopleRepository;
use App\Repository\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReferentController extends AbstractController
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Nouveau service référent.
     *
     * @Route("group/{group_id}/referent/new", name="group_referent_new", methods="GET|POST")
     * @Route("suppport/{support_id}/referent/new", name="support_referent_new", methods="GET|POST")
     * @ParamConverter("groupPeople", options={"id" = "group_id"})
     * @ParamConverter("supportGroup", options={"id" = "support_id"})
     */
    public function newReferent(?int $group_id, ?int $support_id, Request $request, GroupPeopleRepository $repoGroupPeople, SupportGroupRepository $repoSupport): Response
    {
        $support = $support_id ? $repoSupport->findSupportById($support_id) : null;
        $groupPeople = $group_id ? $repoGroupPeople->findGroupPeopleById($group_id) : $support->getGroupPeople();
        $referent = new Referent();

        $form = ($this->createForm(ReferentType::class, $referent))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createReferent($groupPeople, $referent, $support);
        }

        return $this->render('app/referent/referent.html.twig', [
            'group_people' => $groupPeople,
            'support' => $support,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un service référent.
     *
     * @Route("referent/{id}/edit", name="group_referent_edit", methods="GET|POST")
     * @Route("support/{support_id}/referent/{id}/edit", name="support_referent_edit", methods="GET|POST")
     * @ParamConverter("supportGroup", options={"id" = "support_id"})
     */
    public function editReferent(Referent $referent, ?int $support_id = null, Request $request, SupportGroupRepository $repoSupport): Response
    {
        $form = ($this->createForm(ReferentType::class, $referent))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', "Le service social {$referent->getName()} est mis à jour.");

            $this->discache($referent->getGroupPeople());
        }

        return $this->render('app/referent/referent.html.twig', [
            'group_people' => $referent->getGroupPeople(),
            'support' => $support_id ? $repoSupport->findSupportById($support_id) : null,
            'form' => $form->createView(),
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

        $this->addFlash('warning', "Le service social $name est supprimé.");

        $this->discache($referent->getGroupPeople());

        return $this->redirectToRoute('group_people_show', [
            'id' => $referent->getGroupPeople()->getId(),
        ]);
    }

    /**
     * Crée un service référent une fois le formulaire soumis et validé.
     */
    protected function createReferent(GroupPeople $groupPeople, Referent $referent, ?SupportGroup $support = null): Response
    {
        $referent->setGroupPeople($groupPeople);

        $this->manager->persist($referent);
        $this->manager->flush();

        $this->addFlash('success', "Le service social {$referent->getName()} est créé.");

        $this->discache($groupPeople);

        if ($support) {
            return $this->redirectToRoute('support_referent_edit', [
                'support_id' => $support->getId(),
                'id' => $referent->getId(),
            ]);
        }

        return $this->redirectToRoute('group_referent_edit', [
            'id' => $referent->getId(),
        ]);
    }

    /**
     * Supprime les référents en cache du groupe.
     */
    protected function discache(GroupPeople $groupPeople): bool
    {
        return (new FilesystemAdapter())->deleteItem(GroupPeople::CACHE_GROUP_REFERENTS_KEY.$groupPeople->getId());
    }
}
