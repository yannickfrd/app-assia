<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Service\Calendar;
use App\Entity\RdvSearch;

use App\Entity\SupportGroup;
use App\Form\Support\Rdv\RdvType;

use App\Repository\RdvRepository;

use App\Form\Support\Rdv\RdvSearchType;
use App\Repository\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RdvController extends AbstractController
{
    private $manager;
    private $repo;
    private $currentUser;

    public function __construct(EntityManagerInterface $manager, RdvRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
        $this->currentUser = $security->getUser();
    }

    /**
     * Affiche le calendrier (vue mensuelle)
     * 
     * @Route("/calendar/{year}/{month}", name="calendar_show", requirements={"year" : "[0-9]*", "month" : "[0-9]*"}, methods="GET")
     * @Route("/calendar", name="calendar")
     * @Route("support/{id}/calendar/{year}/{month}", name="support_calendar_show", requirements={"year" : "[0-9]*", "month" : "[0-9]*"}, methods="GET")
     * @Route("support/{id}/calendar", name="support_calendar")
     * @return Response
     */
    public function showCalendar($id = null, SupportGroupRepository $supportRepo, $year = null, $month = null, RdvRepository $repo, Request $request): Response
    {
        $supportGroup = $supportRepo->findSupportById($id);

        $calendar = new Calendar($year, $month);

        $rdvs = $repo->FindRdvsBetweenByDay($calendar->getFirstMonday(), $calendar->getLastday(), $supportGroup);

        $rdv = new Rdv();

        $form = $this->createForm(RdvType::class, $rdv);
        $form->handleRequest($request);

        return $this->render("app/rdv/calendar.html.twig", [
            "calendar" => $calendar,
            "rdvs" => $rdvs,
            "support" => $supportGroup ?? null,
            "form" => $form->createView()
        ]);
    }

    /**
     * Affiche un jour du mois (vue journalière)
     * 
     * @Route("/calendar/day/{year}/{month}/{day}", name="calendar_day_show", requirements={"year" : "[0-9]*", "month" : "[0-9]*", "day" : "[0-9]*"}, methods="GET")
     * @Route("/calendar/day", name="calendar_day")
     * @return Response
     */
    public function showDay($year = null, $month = null, $day = null, RdvRepository $repo, Request $request): Response
    {
        $startDay = new \Datetime($year . "-" . $month . "-" . $day);
        $endDay = new \Datetime($year . "-" . $month . "-" . ($day + 1));

        $rdvs = $repo->findRdvsBetween($startDay, $endDay, null);

        $rdv = new Rdv();

        $form = $this->createForm(RdvType::class, $rdv);
        $form->handleRequest($request);

        return $this->render("app/rdv/day.html.twig", [
            "rdvs" => $rdvs,
            "form" => $form->createView()
        ]);
    }

    /**
     * Liste des RDVs
     * 
     * @Route("support/{id}/rdvs", name="rdvs")
     *
     * @param SupportGroup $supportGroup
     * @param RdvSearch $rdvSearch
     * @param Rdv $rdv
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function listRdv(SupportGroup $supportGroup, RdvSearch $rdvSearch = null, Request $request, PaginatorInterface $paginator): Response
    {
        // $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $rdvSearch = new RdvSearch;

        $formSearch = $this->createForm(RdvSearchType::class, $rdvSearch);
        $formSearch->handleRequest($request);

        $rdvs =  $this->paginate($paginator, $supportGroup->getId(), $rdvSearch, $request);

        $rdv = new Rdv();

        $form = $this->createForm(RdvType::class, $rdv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->createRdv($supportGroup->getId(), $rdv);
        }

        return $this->render("app/rdv/listRdvs.html.twig", [
            "support" => $supportGroup,
            "form_search" => $formSearch->createView(),
            "form" => $form->createView(),
            "rdvs" => $rdvs ?? null,
        ]);
    }


    /**
     * Crée le RDV
     * 
     * @Route("support/{id}/rdv/new", name="support_rdv_new")
     * @Route("rdv/new", name="rdv_new")
     * @param Rdv $rdv
     * @param Request $request
     * @return Response
     */
    public function newRdv(SupportGroup $supportGroup = null, Request $request): Response
    {
        // $this->denyAccessUnlessGranted("CREATE", $rdv);

        $rdv = new Rdv();

        $form = $this->createForm(RdvType::class, $rdv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->createRdv($supportGroup, $rdv);
        }

        return $this->error();
    }

    /**
     * Modifie le RDV
     * 
     * @Route("rdv/{id}/get", name="rdv_get")
     * @param Rdv $rdv
     * @param Request $request
     * @return Response
     */
    public function getRdv(Rdv $rdv, RdvRepository $repo): Response
    {
        if ($this->denyAccessUnlessGranted("EDIT", $rdv)) {
        };

        $rdv = $repo->find($rdv->getId());

        // Obtenir le nom du suivi
        if ($rdv->getSupportGroup()) {
            $supportFullname = "";
            foreach ($rdv->getSupportGroup()->getGroupPeople()->getRolePerson() as $rolePerson) {
                if ($rolePerson->getHead() == true) {
                    $supportFullname = $rolePerson->getPerson()->getFullname();
                }
            };
        }

        return $this->json([
            "code" => 200,
            "action" => "show",
            "data" => [
                "title" => $rdv->getTitle(),
                "supportFullname" => $supportFullname ?? null,
                "start" => $rdv->getStart()->format("Y-m-d\TH:i"),
                "end" => $rdv->getEnd()->format("Y-m-d\TH:i"),
                "location" => $rdv->getLocation(),
                "status" => $rdv->getStatus(),
                "content" => $rdv->getContent(),
                "createdBy" => $rdv->getCreatedBy()->getFullname()
            ]
        ], 200);
    }

    /**
     * Modifie le RDV
     * 
     * @Route("rdv/{id}/edit", name="rdv_edit")
     * @param Rdv $rdv
     * @param Request $request
     * @return Response
     */
    public function editRdv(Rdv $rdv, Request $request): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $rdv);

        $form = $this->createForm(RdvType::class, $rdv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->updateRdv($rdv, "update");
        }
        return $this->error();
    }

    /**
     * Supprime le RDV
     * 
     * @Route("rdv/{id}/delete", name="rdv_delete")
     * @param Rdv $rdv
     * @return Response
     */
    public function deleteRdv(Rdv $rdv): Response
    {
        $this->denyAccessUnlessGranted("DELETE", $rdv);

        $this->manager->remove($rdv);
        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => "delete",
            "alert" => "warning",
            "msg" => "Le RDV a été supprimé.",
        ], 200);
    }

    // Pagination de la liste des RDVs
    protected function paginate($paginator, $supportGroupId, $rdvSearch, $request)
    {
        $rdvs =  $paginator->paginate(
            $this->repo->findAllRdvsQuery($supportGroupId, $rdvSearch),
            $request->query->getInt("page", 1), // page number
            6 // limit per page
        );
        $rdvs->setCustomParameters([
            "align" => "right", // align pagination
        ]);

        return $rdvs;
    }

    // Crée le RDV une fois le formulaire soumis et validé
    protected function createRdv($supportGroup = null, $rdv)
    {
        $rdv->setSupportGroup($supportGroup)
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->currentUser)
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->currentUser);

        $this->manager->persist($rdv);
        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => "create",
            "alert" => "success",
            "msg" => "Le RDV a été enregistré.",
            "data" => [
                "rdvId" => $rdv->getId(),
                "title" => $rdv->getTitle(),
                "day" => $rdv->getStart()->format("Y-m-d"),
                "start" => $rdv->getStart()->format("H:i")
            ]
        ], 200);
    }

    // Met à jour le RDV une fois le formulaire soumis et validé
    protected function updateRdv($rdv, $typeSave)
    {
        $rdv->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->currentUser);

        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => $typeSave,
            "alert" => "success",
            "msg" => "Le RDV a été modifié.",
            "data" => [
                "rdvId" => $rdv->getId(),
                "title" => $rdv->getTitle(),
                "day" => $rdv->getStart()->format("Y-m-d"),
                "start" => $rdv->getStart()->format("H:i")
            ]
        ], 200);
    }

    // Retourne une erreur
    protected function error()
    {
        return $this->json([
            "code" => 403,
            "action" => "error",
            "alert" => "danger",
            "msg" => "Une erreur s'est produite.",
        ], 200);
    }


    protected function objectToArray($object)
    {
        if (is_array($object) || is_object($object)) {

            $result = [];

            foreach ((array) $object as $key => $value) {
                $result[$key] = $value;
            }

            return $result;
        }

        return $object;
    }
}
