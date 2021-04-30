<?php

namespace App\Controller\Organization;

use App\Entity\Organization\Referent;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use App\Form\Organization\Referent\ReferentType;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\Support\SupportGroupRepository;
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
     * @Route("/group/{group_id}/referent/new", name="group_referent_new", methods="GET|POST")
     * @Route("/suppport/{support_id}/referent/new", name="support_referent_new", methods="GET|POST")
     * @ParamConverter("peopleGroup", options={"id" = "group_id"})
     * @ParamConverter("supportGroup", options={"id" = "support_id"})
     */
    public function newReferent(
        ?int $group_id,
        ?int $support_id,
        Request $request,
        PeopleGroupRepository $peopleGroupRepo,
        SupportGroupRepository $supportRepo
    ): Response {
        $support = $support_id ? $supportRepo->findSupportById($support_id) : null;
        $peopleGroup = $group_id ? $peopleGroupRepo->findPeopleGroupById($group_id) : $support->getPeopleGroup();

        $form = $this->createForm(ReferentType::class, $referent = new Referent())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createReferent($peopleGroup, $referent, $support);
        }

        return $this->render('app/organization/referent/referent.html.twig', [
            'people_group' => $peopleGroup,
            'support' => $support,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un service référent.
     *
     * @Route("/referent/{id}/edit", name="group_referent_edit", methods="GET|POST")
     * @Route("/support/{support_id}/referent/{id}/edit", name="support_referent_edit", methods="GET|POST")
     * @ParamConverter("supportGroup", options={"id" = "support_id"})
     */
    public function editReferent(
        Referent $referent,
        ?int $support_id = null,
        Request $request,
        SupportGroupRepository $supportRepo
    ): Response {
        $form = $this->createForm(ReferentType::class, $referent)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', "Le service social {$referent->getName()} est mis à jour.");

            $this->discache($referent->getPeopleGroup());
        }

        return $this->render('app/organization/referent/referent.html.twig', [
            'people_group' => $referent->getPeopleGroup(),
            'support' => $support_id ? $supportRepo->findSupportById($support_id) : null,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Supprime un service référent.
     *
     * @Route("/referent/{id}/delete", name="referent_delete", methods="GET")
     * @Route("/support/{supportId}/referent/{id}/delete", name="support_referent_delete", methods="GET")
     */
    public function deleteReferent(int $supportId = null, Referent $referent): Response
    {
        $referent->setUpdatedBy($this->getUser());
        $this->manager->flush();

        $this->manager->remove($referent);
        $this->manager->flush();

        $name = $referent->getName();

        $this->addFlash('warning', "Le service social $name est supprimé.");

        $this->discache($referent->getPeopleGroup());

        if ($supportId) {
            return $this->redirectToRoute('support_view', ['id' => $supportId]);
        }

        return $this->redirectToRoute('people_group_show', ['id' => $referent->getPeopleGroup()->getId()]);
    }

    /**
     * Crée un service référent une fois le formulaire soumis et validé.
     */
    protected function createReferent(PeopleGroup $peopleGroup, Referent $referent, ?SupportGroup $support = null): Response
    {
        $referent->setPeopleGroup($peopleGroup);

        $this->manager->persist($referent);
        $this->manager->flush();

        $this->addFlash('success', "Le service social {$referent->getName()} est créé.");

        $this->discache($peopleGroup);

        if ($support) {
            return $this->redirectToRoute('support_referent_edit', [
                'support_id' => $support->getId(),
                'id' => $referent->getId(),
            ]);
        }

        return $this->redirectToRoute('group_referent_edit', ['id' => $referent->getId()]);
    }

    /**
     * Supprime les référents en cache du groupe.
     */
    protected function discache(PeopleGroup $peopleGroup): bool
    {
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->deleteItem(PeopleGroup::CACHE_GROUP_REFERENTS_KEY.$peopleGroup->getId());
    }
}
