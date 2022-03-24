<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Event\Rdv;
use App\Form\Event\RdvSearchType;
use App\Form\Event\RdvType;
use App\Form\Event\SupportRdvSearchType;
use App\Form\Model\Support\RdvSearch;
use App\Form\Model\Support\SupportRdvSearch;
use App\Repository\Event\RdvRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Security\CurrentUserService;
use App\Service\Api\ApiCalendarRouter;
use App\Service\Event\RdvManager;
use App\Service\Export\RdvExport;
use App\Service\Pagination;
use App\Service\Rdv\RdvPaginator;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RdvController extends AbstractController
{
    use ErrorMessageTrait;

    /**
     * @Route("/rdvs", name="rdv_index", methods="GET|POST")
     */
    public function index(
        Request $request,
        Pagination $pagination,
        CurrentUserService $currentUser,
        RdvRepository $rdvRepo
    ): Response {
        $form = $this->createForm(RdvSearchType::class, $search = new RdvSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            $rdvs = $rdvRepo->findRdvsToExport($search);

            if (!$rdvs) {
                $this->addFlash('warning', 'Aucun résultat à exporter.');

                return $this->redirectToRoute('rdv_index');
            }

            return (new RdvExport())->exportData($rdvs);
        }

        $formRdv = $this->createForm(RdvType::class, (new Rdv())->addUser($this->getUser()));

        return $this->render('app/rdv/rdv_index.html.twig', [
            'form' => $form->createView(),
            'form_rdv' => $formRdv->createView(),
            'rdvs' => $pagination->paginate($rdvRepo->findRdvsQuery($search, $currentUser), $request, 10),
        ]);
    }

    /**
     * With SupportGroup.
     *
     * @Route("/support/{id}/rdvs", name="support_rdv_index", methods="GET")
     */
    public function supportRdvsIndex(
        int $id,
        SupportManager $supportManager,
        Request $request,
        RdvPaginator $rdvPaginator
    ): Response {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportRdvSearchType::class, $search = new SupportRdvSearch(), [
            'service' => $supportGroup->getService(),
        ])
            ->handleRequest($request);

        $formRdv = $this->createForm(RdvType::class, (new Rdv())->addUser($this->getUser()), [
            'support_group' => $supportGroup,
        ]);

        return $this->render('app/rdv/support_rdv_index.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form_rdv' => $formRdv->createView(),
            'rdvs' => $rdvPaginator->getRdvs($supportGroup, $request, $search),
        ]);
    }

    /**
     * Without SupportGroup.
     *
     * @Route("/rdv/create", name="rdv_create", methods="POST")
     */
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ApiCalendarRouter $calendarRouter
    ): JsonResponse {
        $form = $this->createForm(RdvType::class, $rdv = new Rdv())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            RdvManager::addonBeforeFlush($rdv, $form);

            $em->persist($rdv);
            $em->flush();

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => 'Le RDV est enregistré.',
                'rdv' => $rdv,
                'apiUrls' => $calendarRouter->getUrls(
                    'create', $rdv->getId(), (array) $request->request->get('rdv')
                ),
            ], 200, [], ['groups' => Rdv::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * With SupportGroup.
     *
     * @Route("/support/{id}/rdv/create", name="support_rdv_create", methods="POST")
     */
    public function supportRdvCreate(
        int $id,
        SupportGroupRepository $supportGroupRepo,
        Request $request,
        EntityManagerInterface $em,
        ApiCalendarRouter $calendarRouter
    ): JsonResponse {
        $supportGroup = $supportGroupRepo->findSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);


        $rdv = (new Rdv())->setSupportGroup($supportGroup);
        $form = $this->createForm(RdvType::class, $rdv)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            RdvManager::addonBeforeFlush($rdv, $form, $supportGroup);

            $em->persist($rdv);
            $em->flush();

            RdvManager::deleteCacheItems($rdv);

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => 'Le RDV est enregistré.',
                'rdv' => $rdv,
                'apiUrls' => $calendarRouter->getUrls(
                    'create', $rdv->getId(), (array) $request->request->get('rdv')
                ),
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
            'canEdit' => $this->isGranted('EDIT', $rdv),
            'rdv' => $rdv,
        ], 200, [], ['groups' => Rdv::SERIALIZER_GROUPS]);
    }

    /**
     * @Route("/rdv/{id}/edit", name="rdv_edit", methods="POST")
     */
    public function edit(
        Rdv $rdv,
        Request $request,
        EntityManagerInterface $em,
        ApiCalendarRouter $calendarRouter
    ): JsonResponse {
        $this->denyAccessUnlessGranted('EDIT', $rdv);

        $supportGroup = $rdv->getSupportGroup();

        $form = $this->createForm(RdvType::class, $rdv)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            RdvManager::addonBeforeFlush($rdv, $form, $supportGroup);

            $em->flush();

            RdvManager::deleteCacheItems($rdv);

            return $this->json([
                'action' => 'edit',
                'alert' => 'success',
                'msg' => 'Le RDV est modifié.',
                'rdv' => $rdv,
                'apiUrls' => $calendarRouter->getUrls(
                    'update',
                    $rdv->getId(),
                    (array) $request->request->get('rdv')
                ),
            ], 200, [], ['groups' => Rdv::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/rdv/{id}/delete", name="rdv_delete", methods="GET")
     * @IsGranted("DELETE", subject="rdv")
     */
    public function delete(Rdv $rdv, EntityManagerInterface $em, ApiCalendarRouter $calendarRouter): JsonResponse
    {
        $rdvId = $rdv->getId();

        $em->remove($rdv);
        $em->flush();

        RdvManager::deleteCacheItems($rdv);

        return $this->json([
            'action' => 'delete',
            'rdvId' => $rdvId,
            'alert' => 'warning',
            'msg' => 'Le RDV est supprimé.',
            'apiUrls' => $calendarRouter->getUrls('delete', $rdvId, [], [
                'google' => $rdv->getGoogleEventId(),
                'outlook' => $rdv->getOutlookEventId(),
            ]),
        ]);
    }
}
