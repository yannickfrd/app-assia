<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Event\Rdv;
use App\Entity\Support\SupportGroup;
use App\Form\Event\RdvSearchType;
use App\Form\Event\RdvType;
use App\Form\Event\SupportRdvSearchType;
use App\Form\Model\Event\EventSearch;
use App\Repository\Event\RdvRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Api\ApiCalendarRouter;
use App\Service\Event\RdvManager;
use App\Service\Event\RdvPaginator;
use App\Service\Export\RdvExport;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RdvController extends AbstractController
{
    use ErrorMessageTrait;

    /**
     * @Route("/rdvs", name="rdv_index", methods="GET|POST")
     */
    public function index(Request $request, RdvPaginator $rdvPaginator, RdvRepository $rdvRepo): Response
    {
        $form = $this->createForm(RdvSearchType::class, $search = new EventSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            if ($rdvs = $rdvRepo->findRdvsToExport($search, $this->getUser())) {
                return (new RdvExport())->exportData($rdvs);
            }

            $this->addFlash('warning', 'no_result_to_export');
        }

        $formRdv = $this->createForm(RdvType::class, (new Rdv())->addUser($this->getUser()));

        return $this->renderForm('app/rdv/rdv_index.html.twig', [
            'form' => $form,
            'form_rdv' => $formRdv,
            'rdvs' => $rdvPaginator->paginate($request, $search),
        ]);
    }

    /**
     * With SupportGroup.
     *
     * @Route("/support/{id}/rdvs", name="support_rdv_index", methods="GET|POST")
     */
    public function indexSupportRdvs(
        int $id,
        SupportManager $supportManager,
        Request $request,
        RdvPaginator $rdvPaginator,
        RdvRepository $rdvRepo
    ): Response {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportRdvSearchType::class, $search = new EventSearch(), [
            'service' => $supportGroup->getService(),
        ])->handleRequest($request);

        if ($search->getExport()) {
            if ($rdvs = $rdvRepo->findRdvsToExport($search, $this->getUser(), $supportGroup)) {
                return (new RdvExport())->exportData($rdvs);
            }

            $this->addFlash('warning', 'no_result_to_export');
        }

        $formRdv = $this->createForm(RdvType::class, (new Rdv())->addUser($this->getUser()), [
            'support_group' => $supportGroup,
        ]);

        return $this->renderForm('app/rdv/support_rdv_index.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch,
            'form_rdv' => $formRdv,
            'rdvs' => $rdvPaginator->paginate($request, $search, $supportGroup),
        ]);
    }

    /**
     * @Route("/rdv/create", name="rdv_create", methods="POST")
     * @Route("/support/{id}/rdv/create", name="support_rdv_create", methods="POST")
     */
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ApiCalendarRouter $calendarRouter,
        TranslatorInterface $translator,
        ?int $id,
    ): JsonResponse {
        $rdv = new Rdv();

        // Add SupportGroup to Rdv if ID exists
        if (null !== $id) {
            /** @var SupportGroupRepository $supportGroupRepo */
            $supportGroupRepo = $em->getRepository(SupportGroup::class);
            $supportGroup = $supportGroupRepo->findSupportById($id);

            $this->denyAccessUnlessGranted('EDIT', $supportGroup);

            $rdv->setSupportGroup($supportGroup);
        }

        $form = $this->createForm(RdvType::class, $rdv)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            RdvManager::addonBeforeFlush($rdv);

            $em->persist($rdv);
            $em->flush();

            RdvManager::deleteCacheItems($rdv);

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => $translator->trans('rdv.created_successfully', ['rdv_title' => $rdv->getTitle()], 'app'),
                'rdv' => $rdv,
                'apiUrls' => $calendarRouter->getUrls('create', $rdv),
            ], 200, [], ['groups' => Rdv::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/rdv/{id}/show", name="rdv_show", methods="GET")
     */
    public function show(int $id, RdvRepository $rdvRepo): JsonResponse
    {
        $rdv = $rdvRepo->findRdv($id);

        $this->denyAccessUnlessGranted('VIEW', $rdv);

        return $this->json([
            'action' => 'show',
            'rdv' => $rdv,
        ], 200, [], ['groups' => Rdv::SERIALIZER_GROUPS]);
    }

    /**
     * @Route("/rdv/{id}/edit", name="rdv_edit", methods="POST")
     */
    public function edit(
        int $id,
        RdvRepository $rdvRepo,
        Request $request,
        EntityManagerInterface $em,
        ApiCalendarRouter $calendarRouter,
        TranslatorInterface $translator
    ): JsonResponse {
        $rdv = $rdvRepo->findRdv($id);

        $this->denyAccessUnlessGranted('EDIT', $rdv);

        $form = $this->createForm(RdvType::class, $rdv)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            RdvManager::addonBeforeFlush($rdv);

            $em->flush();

            RdvManager::deleteCacheItems($rdv);

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => $translator->trans('rdv.updated_successfully', ['rdv_title' => $rdv->getTitle()], 'app'),
                'rdv' => $rdv,
                'apiUrls' => $calendarRouter->getUrls('update', $rdv),
            ], 200, [], ['groups' => Rdv::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/rdv/{id}/delete", name="rdv_delete", methods="DELETE")
     */
    public function delete(
        int $id,
        RdvRepository $rdvRepo,
        EntityManagerInterface $em,
        ApiCalendarRouter $calendarRouter,
        TranslatorInterface $translator
    ): JsonResponse {
        $rdv = $rdvRepo->findRdv($id);

        $this->denyAccessUnlessGranted('DELETE', $rdv);

        $em->remove($rdv);
        $em->flush();

        RdvManager::deleteCacheItems($rdv);

        return $this->json([
            'action' => 'delete',
            'rdv' => ['id' => $id],
            'alert' => 'warning',
            'msg' => $translator->trans('rdv.deleted_successfully', ['rdv_title' => $rdv->getTitle()], 'app'),
            'apiUrls' => $calendarRouter->getUrls('delete', $rdv),
        ]);
    }

    /**
     * @Route("/rdv/{id}/restore", name="rdv_restore", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function restore(
        int $id,
        RdvRepository $rdvRepo,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): JsonResponse {
        $rdv = $rdvRepo->findRdv($id, true);

        $rdv->setDeletedAt(null);
        $em->flush();

        RdvManager::deleteCacheItems($rdv);

        return $this->json([
            'action' => 'restore',
            'alert' => 'success',
            'msg' => $translator->trans('rdv.restored_successfully', ['rdv_title' => $rdv->getTitle()], 'app'),
            'rdv' => ['id' => $rdv->getId()],
        ]);
    }
}
