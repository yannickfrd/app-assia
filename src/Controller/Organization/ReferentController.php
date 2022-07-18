<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization\Referent;
use App\Entity\People\PeopleGroup;
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
use Symfony\Contracts\Translation\TranslatorInterface;

final class ReferentController extends AbstractController
{
    private $em;
    private $translator;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @Route("/group/{group_id}/referent/new", name="group_referent_new", methods="GET|POST")
     * @Route("/support/{support_id}/referent/new", name="support_referent_new", methods="GET|POST")
     * @ParamConverter("peopleGroup", options={"id" = "group_id"})
     * @ParamConverter("supportGroup", options={"id" = "support_id"})
     */
    public function new(
        Request $request,
        PeopleGroupRepository $peopleGroupRepo,
        SupportGroupRepository $supportRepo,
        ?int $group_id,
        ?int $support_id
    ): Response {
        $support = $support_id ? $supportRepo->findSupportById($support_id) : null;
        $peopleGroup = $group_id ? $peopleGroupRepo->findPeopleGroupById($group_id) : $support->getPeopleGroup();

        $form = $this->createForm(ReferentType::class, $referent = new Referent())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $referent->setPeopleGroup($peopleGroup);

            $this->em->persist($referent);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('referent.created_successfully',
                ['referent_name' => $referent->getName()], 'app')
            );

            $this->deleteCacheItems($peopleGroup);

            if ($support) {
                return $this->redirectToRoute('support_referent_edit', [
                    'support_id' => $support->getId(),
                    'id' => $referent->getId(),
                ]);
            }

            return $this->redirectToRoute('group_referent_edit', ['id' => $referent->getId()]);
        }

        return $this->render('app/organization/referent/referent_edit.html.twig', [
            'people_group' => $peopleGroup,
            'support' => $support,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/referent/{id}/edit", name="group_referent_edit", methods="GET|POST")
     * @Route("/support/{support_id}/referent/{id}/edit", name="support_referent_edit", methods="GET|POST")
     * @ParamConverter("supportGroup", options={"id" = "support_id"})
     */
    public function edit(
        Referent $referent,
        Request $request,
        SupportGroupRepository $supportRepo,
        ?int $support_id = null
    ): Response {
        $form = $this->createForm(ReferentType::class, $referent)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('referent.updated_successfully',
                ['referent_name' => $referent->getName()], 'app')
            );

            $this->deleteCacheItems($referent->getPeopleGroup());
        }

        return $this->render('app/organization/referent/referent_edit.html.twig', [
            'people_group' => $referent->getPeopleGroup(),
            'support' => $support_id ? $supportRepo->findSupportById($support_id) : null,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/referent/{id}/delete", name="referent_delete", methods="GET")
     * @Route("/support/{supportId}/referent/{id}/delete", name="support_referent_delete", methods="GET")
     */
    public function delete(Referent $referent, int $supportId = null): Response
    {
        $referent->setUpdatedBy($this->getUser());
        $this->em->flush();

        $this->em->remove($referent);
        $this->em->flush();

        $this->addFlash('warning', $this->translator->trans('referent.deleted_successfully', [
            'referent_name' => $referent->getName()
        ], 'app'));

        $this->deleteCacheItems($referent->getPeopleGroup());

        if ($supportId) {
            return $this->redirectToRoute('support_show', ['id' => $supportId]);
        }

        return $this->redirectToRoute('people_group_show', ['id' => $referent->getPeopleGroup()->getId()]);
    }

    /**
     * Supprime les référents en cache du groupe.
     */
    protected function deleteCacheItems(PeopleGroup $peopleGroup): bool
    {
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->deleteItem(PeopleGroup::CACHE_GROUP_REFERENTS_KEY.$peopleGroup->getId());
    }
}
