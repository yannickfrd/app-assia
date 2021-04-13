<?php

namespace App\Controller\Support;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\User;
use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use App\EntityManager\SupportManager;
use App\Form\Model\Support\RdvSearch;
use App\Form\Model\Support\SupportRdvSearch;
use App\Form\Support\Rdv\RdvSearchType;
use App\Form\Support\Rdv\RdvType;
use App\Form\Support\Rdv\SupportRdvSearchType;
use App\Repository\Support\RdvRepository;
use App\Security\CurrentUserService;
use App\Service\Calendar;
use App\Service\Export\RdvExport;
use App\Service\Pagination;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RdvController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, RdvRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des rendez-vous.
     *
     * @Route("/rdvs", name="rdvs", methods="GET|POST")
     */
    public function viewListRdvs(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $search = new RdvSearch();
        if (User::STATUS_SOCIAL_WORKER === $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = ($this->createForm(RdvSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/support/rdv/listRdvs.html.twig', [
            'form' => $form->createView(),
            'rdvs' => $pagination->paginate($this->repo->findRdvsQuery($search, $currentUser), $request, 10) ?? null,
        ]);
    }

    /**
     * Liste des rendez-vous.
     *
     * @Route("support/{id}/rdvs", name="support_rdvs", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function viewSupportListRdvs(int $id, SupportManager $supportManager, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $search = new SupportRdvSearch();

        $formSearch = ($this->createForm(SupportRdvSearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/support/rdv/supportRdvs.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'rdvs' => $this->getRdvs($supportGroup, $request, $search, $pagination),
        ]);
    }

    /**
     * Affiche l'agenda de l'utilisateur (vue mensuelle).
     *
     * @Route("/calendar/month/{year}/{month}", name="calendar_show", methods="GET", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * })
     * @Route("/calendar/month", name="calendar", methods="GET")
     */
    public function showCalendar(int $year = null, int $month = null): Response
    {
        $form = $this->createForm(RdvType::class, (new Rdv())->setUser($this->getUser()));

        return $this->render('app/support/rdv/calendar.html.twig', [
            'calendar' => $calendar = new Calendar($year, $month),
            'form' => $form->createView(),
            'rdvs' => $this->repo->findRdvsBetweenByDay(
                $calendar->getFirstMonday(),
                $calendar->getLastday(),
                null,
                $this->getUser(),
            ),
        ]);
    }

    /**
     * Affiche l'agenda d'un suivi (vue mensuelle).
     *
     * @Route("/support/{id}/calendar//month/{year}/{month}", name="support_calendar_show", methods="GET", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * })
     * @Route("/support/{id}/calendar/month", name="support_calendar", methods="GET")
     *
     * @param int $id // SupportGroup
     */
    public function showSupportCalendar(int $id, SupportManager $supportManager, $year = null, $month = null): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $form = $this->createForm(RdvType::class, (new Rdv())->setUser($this->getUser()));

        return $this->render('app/support/rdv/calendar.html.twig', [
            'support' => $supportGroup,
            'calendar' => $calendar = new Calendar($year, $month),
            'form' => $form->createView(),
            'rdvs' => $this->repo->findRdvsBetweenByDay(
                $calendar->getFirstMonday(),
                $calendar->getLastday(),
                $supportGroup),
        ]);
    }

    /**
     * Affiche un jour du mois (vue journalière).
     *
     * @Route("/calendar/day/{year}/{month}/{day}", name="calendar_day_show", methods="GET", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * "day" : "[1-9]|[0-3][0-9]",
     * })
     */
    public function showDay(int $year = null, int $month = null, int $day = null, Request $request): Response
    {
        $startDay = new \Datetime($year.'-'.$month.'-'.$day);
        $endDay = new \Datetime($year.'-'.$month.'-'.($day + 1));

        $form = ($this->createForm(RdvType::class, new Rdv()))
            ->handleRequest($request);

        return $this->render('app/support/rdv/day.html.twig', [
            'form' => $form->createView(),
            'rdvs' => $this->repo->findRdvsBetween($startDay, $endDay, null, $this->getUser()),
        ]);
    }

    /**
     * Nouveau rendez-vous.
     *
     * @Route("support/{id}/rdv/new", name="support_rdv_new", methods="POST")
     * @Route("rdv/new", name="rdv_new", methods="POST")
     */
    public function newRdv(SupportGroup $supportGroup = null, Rdv $rdv = null, Request $request): Response
    {
        if ($supportGroup) {
            $this->denyAccessUnlessGranted('EDIT', $supportGroup);
        }

        $form = ($this->createForm(RdvType::class, $rdv = new Rdv()))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createRdv($rdv, $supportGroup ?? null);
        }

        return $this->error();
    }

    /**
     * Voir le RDV.
     *
     * @Route("rdv/{id}/get", name="rdv_get", methods="GET")
     */
    public function getRdv(int $id): Response
    {
        $rdv = $this->repo->findRdv($id);

        $this->denyAccessUnlessGranted('VIEW', $rdv);

        $supportGroup = $rdv->getSupportGroup();

        return $this->json([
            'code' => 200,
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
        ], 200);
    }

    /**
     * Modifie le RDV.
     *
     * @Route("rdv/{id}/edit", name="rdv_edit", methods="POST")
     */
    public function editRdv(Rdv $rdv, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $rdv);

        $form = ($this->createForm(RdvType::class, $rdv))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateRdv($rdv, 'update');
        }

        return $this->error();
    }

    /**
     * Supprime le RDV.
     *
     * @Route("rdv/{id}/delete", name="rdv_delete", methods="GET")
     * @IsGranted("DELETE", subject="rdv")
     */
    public function deleteRdv(Rdv $rdv): Response
    {
        $this->manager->remove($rdv);
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'delete',
            'rdv' => ['id' => $rdv->getId()],
            'alert' => 'warning',
            'msg' => 'Le RDV est supprimé.',
        ], 200);
    }

    /**
     * Donne les rendez-vous du suivi.
     */
    protected function getRdvs(SupportGroup $supportGroup, Request $request, SupportRdvSearch $search, Pagination $pagination)
    {
        // Si filtre ou tri utilisé, n'utilise pas le cache.
        if ($request->query->count() > 0) {
            return $pagination->paginate($this->repo->findRdvsQueryOfSupport($supportGroup->getId(), $search), $request);
        }

        // Sinon, récupère les rendez-vous en cache.
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(SupportGroup::CACHE_SUPPORT_RDVS_KEY.$supportGroup->getId(),
            function (CacheItemInterface $item) use ($supportGroup, $pagination, $search, $request) {
                $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

                return $pagination->paginate($this->repo->findRdvsQueryOfSupport($supportGroup->getId(), $search), $request);
            }
        );
    }

    /**
     * Crée le RDV une fois le formulaire soumis et validé.
     */
    protected function createRdv(Rdv $rdv, SupportGroup $supportGroup = null): Response
    {
        $rdv->setSupportGroup($supportGroup);

        if ($supportGroup) {
            $supportGroup->setUpdatedAt(new \DateTime());
        }

        $this->manager->persist($rdv);
        $this->manager->flush();

        $this->discache($rdv->getSupportGroup());

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'Le RDV est enregistré.',
            'rdv' => [
                'id' => $rdv->getId(),
                'title' => $rdv->getTitle(),
                'day' => $rdv->getStart()->format('Y-m-d'),
                'start' => $rdv->getStart()->format('H:i'),
            ],
        ], 200);
    }

    /**
     * Met à jour le RDV une fois le formulaire soumis et validé.
     */
    protected function updateRdv(Rdv $rdv, string $typeSave): Response
    {
        $this->manager->flush();

        $this->discache($rdv->getSupportGroup(), true);

        return $this->json([
            'code' => 200,
            'action' => $typeSave,
            'alert' => 'success',
            'msg' => 'Le RDV est modifié.',
            'rdv' => [
                'id' => $rdv->getId(),
                'title' => $rdv->getTitle(),
                'day' => $rdv->getStart()->format('Y-m-d'),
                'start' => $rdv->getStart()->format('H:i'),
            ],
        ], 200);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(RdvSearch $search)
    {
        $supports = $this->repo->findRdvsToExport($search);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new RdvExport())->exportData($supports);
    }

    /**
     * Retourne une erreur.
     */
    protected function error(): Response
    {
        return $this->json([
            'code' => 403,
            'action' => 'error',
            'alert' => 'danger',
            'msg' => "Une erreur s'est produite.",
        ], 200);
    }

    /**
     * Supprime les rendez-vous en cache du suivi et de l'utlisateur.
     */
    protected function discache(?SupportGroup $supportGroup = null, $isUpdate = false): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if ($supportGroup) {
            $cache->deleteItems([
                SupportGroup::CACHE_SUPPORT_LAST_RDV_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_SUPPORT_NEXT_RDV_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_SUPPORT_RDVS_KEY.$supportGroup->getId(),
            ]);
            if (false === $isUpdate) {
                $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_RDVS_KEY.$supportGroup->getId());
            }
        }

        return $cache->deleteItem(User::CACHE_USER_RDVS_KEY.$this->getUser()->getId());
    }
}
