<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\SupportGroup;
use App\Form\Model\RdvSearch;
use App\Form\Model\SupportRdvSearch;
use App\Form\Rdv\RdvSearchType;
use App\Form\Rdv\RdvType;
use App\Form\Rdv\SupportRdvSearchType;
use App\Repository\RdvRepository;
use App\Repository\SupportGroupRepository;
use App\Service\Calendar;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RdvController extends AbstractController
{
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
    public function viewListRdvs(Request $request, RdvSearch $rdvSearch = null, Pagination $pagination): Response
    {
        $rdvSearch = new RdvSearch();

        $form = ($this->createForm(RdvSearchType::class, $rdvSearch))
            ->handleRequest($request);

        return $this->render('app/rdv/listRdvs.html.twig', [
            'rdvSearch' => $rdvSearch,
            'form' => $form->createView(),
            'rdvs' => $pagination->paginate($this->repo->findAllRdvsQuery($rdvSearch), $request) ?? null,
        ]);
    }

    /**
     * Liste des rendez-vous.
     *
     * @Route("support/{id}/rdvs", name="support_rdvs", methods="GET")
     *
     * @param int $id // SupportGroup
     */
    public function viewSupportListRdvs(int $id, SupportGroupRepository $repoSupport, SupportRdvSearch $rdvSearch = null, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $repoSupport->findSupportById($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $rdvSearch = new SupportRdvSearch();

        $formSearch = $this->createForm(SupportRdvSearchType::class, $rdvSearch);
        $formSearch->handleRequest($request);

        return $this->render('app/rdv/supportRdvs.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'rdvs' => $pagination->paginate($this->repo->findAllRdvsQueryFromSupport($supportGroup->getId(), $rdvSearch), $request),
        ]);
    }

    /**
     * Affiche l'agenda de l'utilisateur (vue mensuelle).
     *
     * @Route("/calendar/{year}/{month}", name="calendar_show", requirements={"year" : "[0-9]*", "month" : "[0-9]*"}, methods="GET")
     * @Route("/calendar", name="calendar", methods="GET")
     */
    public function showCalendar(int $year = null, int $month = null): Response
    {
        $calendar = new Calendar($year, $month);

        $form = $this->createForm(RdvType::class, new Rdv());

        return $this->render('app/rdv/calendar.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
            'rdvs' => $this->repo->findRdvsBetweenByDay($calendar->getFirstMonday(), $calendar->getLastday(), null),
        ]);
    }

    /**
     * Affiche l'agenda d'un suivi (vue mensuelle).
     *
     * @Route("/support/{id}/calendar/{year}/{month}", name="support_calendar_show", requirements={"year" : "[0-9]*", "month" : "[0-9]*"}, methods="GET")
     * @Route("/support/{id}/calendar", name="support_calendar", methods="GET")
     *
     * @param int $id // SupportGroup
     */
    public function showSupportCalendar(int $id, SupportGroupRepository $repoSupport, $year = null, $month = null): Response
    {
        $supportGroup = $repoSupport->findSupportById($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $calendar = new Calendar($year, $month);

        $form = $this->createForm(RdvType::class, new Rdv());

        return $this->render('app/rdv/calendar.html.twig', [
            'support' => $supportGroup,
            'calendar' => $calendar,
            'form' => $form->createView(),
            'rdvs' => $this->repo->findRdvsBetweenByDay($calendar->getFirstMonday(), $calendar->getLastday(), $supportGroup),
        ]);
    }

    /**
     * Affiche un jour du mois (vue journalière).
     *
     * @Route("/calendar/day/{year}/{month}/{day}", name="calendar_day_show", requirements={"year" : "[0-9]*", "month" : "[0-9]*", "day" : "[0-9]*"}, methods="GET")
     *
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function showDay($year = null, $month = null, $day = null, Request $request): Response
    {
        $startDay = new \Datetime($year.'-'.$month.'-'.$day);
        $endDay = new \Datetime($year.'-'.$month.'-'.($day + 1));

        $form = ($this->createForm(RdvType::class, new Rdv()))
            ->handleRequest($request);

        return $this->render('app/rdv/day.html.twig', [
            'form' => $form->createView(),
            'rdvs' => $this->repo->findRdvsBetween($startDay, $endDay, null),
        ]);
    }

    /**
     * Nouveau rendez-vous.
     *
     * @Route("support/{id}/rdv/new", name="support_rdv_new", methods="POST")
     * @Route("rdv/new", name="rdv_new", methods="POST")
     *
     * @param int $id  // SupportGroup
     * @param Rdv $rdv
     */
    public function newRdv(int $id = null, SupportGroupRepository $repoSupport, Rdv $rdv = null, Request $request): Response
    {
        if ($id) {
            $supportGroup = $repoSupport->findSupportById($id);
            $this->denyAccessUnlessGranted('EDIT', $supportGroup);
        }

        $rdv = new Rdv();

        $form = ($this->createForm(RdvType::class, $rdv))
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
    public function getRdv(Rdv $rdv): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $rdv);

        // Obtenir le nom de la personne suivie
        if ($rdv->getSupportGroup()) {
            $supportFullname = '';
            foreach ($rdv->getSupportGroup()->getSupportPerson() as $supportPerson) {
                if (true == $supportPerson->getHead()) {
                    $supportFullname = $supportPerson->getPerson()->getFullname();
                }
            }
        }

        return $this->json([
            'code' => 200,
            'action' => 'show',
            'data' => [
                'title' => $rdv->getTitle(),
                'supportFullname' => $supportFullname ?? null,
                'start' => $rdv->getStart()->format("Y-m-d\TH:i"),
                'end' => $rdv->getEnd()->format("Y-m-d\TH:i"),
                'location' => $rdv->getLocation(),
                'status' => $rdv->getStatus(),
                'content' => $rdv->getContent(),
                'createdBy' => $rdv->getCreatedBy()->getFullname(),
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
            'alert' => 'warning',
            'msg' => 'Le RDV a été supprimé.',
        ], 200);
    }

    /**
     * Crée le RDV une fois le formulaire soumis et validé.
     *
     * @param SupportGroup $supportGroup
     *
     * @return Response
     */
    protected function createRdv(Rdv $rdv, SupportGroup $supportGroup = null)
    {
        $now = new \DateTime();

        $rdv->setSupportGroup($supportGroup)
            ->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($rdv);
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'Le RDV a été enregistré.',
            'data' => [
                'rdvId' => $rdv->getId(),
                'title' => $rdv->getTitle(),
                'day' => $rdv->getStart()->format('Y-m-d'),
                'start' => $rdv->getStart()->format('H:i'),
            ],
        ], 200);
    }

    /**
     * Met à jour le RDV une fois le formulaire soumis et validé.
     *
     * @param string $typeSave
     */
    protected function updateRdv(Rdv $rdv, $typeSave): Response
    {
        $rdv->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => $typeSave,
            'alert' => 'success',
            'msg' => 'Le RDV a été modifié.',
            'data' => [
                'rdvId' => $rdv->getId(),
                'title' => $rdv->getTitle(),
                'day' => $rdv->getStart()->format('Y-m-d'),
                'start' => $rdv->getStart()->format('H:i'),
            ],
        ], 200);
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
}
