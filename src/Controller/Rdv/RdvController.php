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
use App\Service\Export\RdvExport;
use App\Service\Pagination;
use App\Service\Rdv\RdvPaginator;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RdvController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $rdvRepo;

    public function __construct(EntityManagerInterface $manager, RdvRepository $rdvRepo)
    {
        $this->manager = $manager;
        $this->rdvRepo = $rdvRepo;
    }

    /**
     * Liste des rendez-vous.
     *
     * @Route("/rdvs", name="rdvs", methods="GET|POST")
     */
    public function viewListRdvs(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $form = $this->createForm(RdvSearchType::class, $search = new RdvSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/rdv/listRdvs.html.twig', [
            'form' => $form->createView(),
            'rdvs' => $pagination->paginate($this->rdvRepo->findRdvsQuery($search, $currentUser), $request, 10),
        ]);
    }

    /**
     * Liste des rendez-vous.
     *
     * @Route("/support/{id}/rdvs", name="support_rdvs", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function viewSupportListRdvs(int $id, SupportManager $supportManager, Request $request, RdvPaginator $rdvPaginator): Response
    {
        if (null === $supportGroup = $supportManager->getSupportGroup($id)) {
            throw $this->createAccessDeniedException('Ce suivi n\'existe pas.');
        }

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportRdvSearchType::class, $search = new SupportRdvSearch())
            ->handleRequest($request);

        return $this->render('app/rdv/supportRdvs.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'rdvs' => $rdvPaginator->getRdvs($supportGroup, $request, $search),
        ]);
    }

    /**
     * Nouveau rendez-vous sans suivi.
     *
     * @Route("/rdv/new", name="rdv_new", methods="POST")
     */
    public function createRdv(Request $request): Response
    {
        $form = $this->createForm(RdvType::class, $rdv = new Rdv())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($rdv);
            $this->manager->flush();

            return $this->json($this->getDataNewRdv($rdv));
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Nouveau rendez-vous d'un suivi.
     *
     * @Route("/support/{id}/rdv/new", name="support_rdv_new", methods="POST")
     */
    public function createSupportRdv(int $id, SupportGroupRepository $supportGroupRepo, Request $request, EventDispatcherInterface $dispatcher): Response
    {
        if (null === $supportGroup = $supportGroupRepo->findSupportById($id)) {
            throw $this->createAccessDeniedException('Ce suivi n\'existe pas.');
        }

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(RdvType::class, $rdv = new Rdv())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rdv->setSupportGroup($supportGroup);

            $this->manager->persist($rdv);
            $this->manager->flush();

            $dispatcher->dispatch(new RdvEvent($rdv), 'rdv.after_create');

            return $this->json($this->getDataNewRdv($rdv));
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Donne un RDV.
     *
     * @Route("/rdv/{id}/get", name="rdv_get", methods="GET")
     */
    public function getRdv(int $id): Response
    {
        if (null === $rdv = $this->rdvRepo->findRdv($id)) {
            throw $this->createAccessDeniedException();
        }

        $this->denyAccessUnlessGranted('VIEW', $rdv);

        $supportGroup = $rdv->getSupportGroup();

        return $this->json([
            'action' => 'show',
            'rdv' => [
                'title' => $rdv->getTitle(),
                'fullnameSupport' => $supportGroup ? $supportGroup->getHeader()->getFullname() : null,
                'start' => $rdv->getStart()->format("Y-m-d\TH:i"),
                'end' => $rdv->getEnd()->format("Y-m-d\TH:i"),
                'location' => $rdv->getLocation(),
                'status' => $rdv->getStatus(),
                'content' => $rdv->getContent(),
                'supportId' => $rdv->getSupportGroup() ? $rdv->getSupportGroup()->getId() : null,
                'createdBy' => $rdv->getCreatedBy()->getFullname(),
                'createdAt' => $rdv->getCreatedAt()->format('d/m/Y à H:i'),
                'updatedBy' => $rdv->getUpdatedBy()->getFullname(),
                'updatedAt' => $rdv->getUpdatedAt()->format('d/m/Y à H:i'),
                'canEdit' => $this->isGranted('EDIT', $rdv),
            ],
        ]);
    }

    /**
     * Modifie le RDV.
     *
     * @Route("/rdv/{id}/edit", name="rdv_edit", methods="POST")
     */
    public function editRdv(Rdv $rdv, Request $request, EventDispatcherInterface $dispatcher): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $rdv);

        $form = $this->createForm(RdvType::class, $rdv)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $dispatcher->dispatch(new RdvEvent($rdv), 'rdv.after_update');

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => 'Le RDV est modifié.',
                'rdv' => [
                    'id' => $rdv->getId(),
                    'title' => $rdv->getTitle(),
                    'day' => $rdv->getStart()->format('Y-m-d'),
                    'start' => $rdv->getStart()->format('H:i'),
                ],
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Supprime le RDV.
     *
     * @Route("/rdv/{id}/delete", name="rdv_delete", methods="GET")
     * @IsGranted("DELETE", subject="rdv")
     */
    public function deleteRdv(Rdv $rdv): Response
    {
        $this->manager->remove($rdv);
        $this->manager->flush();

        return $this->json([
            'action' => 'delete',
            'rdv' => ['id' => $rdv->getId()],
            'alert' => 'warning',
            'msg' => 'Le RDV est supprimé.',
        ]);
    }

    private function getDataNewRdv(Rdv $rdv)
    {
        return [
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'Le RDV est enregistré.',
            'rdv' => [
                'id' => $rdv->getId(),
                'title' => $rdv->getTitle(),
                'day' => $rdv->getStart()->format('Y-m-d'),
                'start' => $rdv->getStart()->format('H:i'),
            ],
        ];
    }

    /**
     * Exporte les données.
     */
    private function exportData(RdvSearch $search)
    {
        $rdvs = $this->rdvRepo->findRdvsToExport($search);

        if (!$rdvs) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('rdvs');
        }

        return (new RdvExport())->exportData($rdvs);
    }
}
