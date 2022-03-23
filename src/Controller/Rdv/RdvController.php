<?php

namespace App\Controller\Rdv;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Support\Rdv;
use App\Event\Rdv\RdvEvent;
use App\Form\Model\Support\RdvSearch;
use App\Form\Model\Support\SupportRdvSearch;
use App\Form\Support\Rdv\RdvSearchType;
use App\Form\Support\Rdv\RdvType;
use App\Form\Support\Rdv\SupportRdvSearchType;
use App\Repository\Support\RdvRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Security\CurrentUserService;
use App\Service\Api\ApiCalendarRouter;
use App\Service\Export\RdvExport;
use App\Service\Pagination;
use App\Service\Rdv\RdvPaginator;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RdvController extends AbstractController
{
    use ErrorMessageTrait;

    private $em;
    private $rdvRepo;
    private $calendarRouter;

    public function __construct(EntityManagerInterface $em, RdvRepository $rdvRepo, ApiCalendarRouter $calendarRouter)
    {
        $this->em = $em;
        $this->rdvRepo = $rdvRepo;
        $this->calendarRouter = $calendarRouter;
    }

    /**
     * @Route("/rdvs", name="rdv_index", methods="GET|POST")
     */
    public function index(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $form = $this->createForm(RdvSearchType::class, $search = new RdvSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            $rdvs = $this->rdvRepo->findRdvsToExport($search);

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
            'rdvs' => $pagination->paginate($this->rdvRepo->findRdvsQuery($search, $currentUser), $request, 10),
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
    public function create(Request $request, EventDispatcherInterface $dispatcher): JsonResponse
    {
        $form = $this->createForm(RdvType::class, $rdv = new Rdv())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new RdvEvent($rdv, null, $form), 'rdv.before_create');

            $this->em->persist($rdv);
            $this->em->flush();

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => 'Le RDV est enregistré.',
                'rdv' => $rdv,
                'apiUrls' => $this->calendarRouter->getUrls(
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
        EventDispatcherInterface $dispatcher
    ): JsonResponse {
        $supportGroup = $supportGroupRepo->findSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $rdv = (new Rdv())->setSupportGroup($supportGroup);
        $form = $this->createForm(RdvType::class, $rdv)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new RdvEvent($rdv, $supportGroup, $form), 'rdv.before_create');

            $this->em->persist($rdv);
            $this->em->flush();

            $dispatcher->dispatch(new RdvEvent($rdv), 'rdv.after_create');

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => 'Le RDV est enregistré.',
                'rdv' => $rdv,
                'apiUrls' => $this->calendarRouter->getUrls(
                    'create', $rdv->getId(), (array) $request->request->get('rdv')
                ),
            ], 200, [], ['groups' => Rdv::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/rdv/{id}/show", name="rdv_show", methods="GET")
     */
    public function show(Rdv $rdv): JsonResponse
    {
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
    public function edit(Rdv $rdv, Request $request, EventDispatcherInterface $dispatcher): JsonResponse
    {
        $this->denyAccessUnlessGranted('EDIT', $rdv);

        $supportGroup = $rdv->getSupportGroup();

        $form = $this->createForm(RdvType::class, $rdv)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new RdvEvent($rdv, $supportGroup, $form), 'rdv.before_update');

            $this->em->flush();

            $dispatcher->dispatch(new RdvEvent($rdv), 'rdv.after_update');

            return $this->json([
                'action' => 'edit',
                'alert' => 'success',
                'msg' => 'Le RDV est modifié.',
                'rdv' => $rdv,
                'apiUrls' => $this->calendarRouter->getUrls(
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
    public function delete(Rdv $rdv, EventDispatcherInterface $dispatcher): JsonResponse
    {
        $rdvId = $rdv->getId();

        $this->em->remove($rdv);
        $this->em->flush();

        $dispatcher->dispatch(new RdvEvent($rdv), 'rdv.after_update');

        return $this->json([
            'action' => 'delete',
            'rdvId' => $rdvId,
            'alert' => 'warning',
            'msg' => 'Le RDV est supprimé.',
            'apiUrls' => $this->calendarRouter->getUrls('delete', $rdvId, [], [
                'google' => $rdv->getGoogleEventId(),
                'outlook' => $rdv->getOutlookEventId(),
            ]),
        ]);
    }
}
