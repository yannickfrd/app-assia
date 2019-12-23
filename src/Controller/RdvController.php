<?php

namespace App\Controller;

use App\Entity\Rdv;
use App\Entity\RdvSearch;
use App\Utils\Calendar;

use App\Entity\SupportGroup;
use App\Form\Support\Rdv\RdvType;

use App\Repository\RdvRepository;

use App\Form\Support\Rdv\RdvSearchType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RdvController extends AbstractController
{
    private $manager;
    private $repo;
    private $currentUser;

    public function __construct(ObjectManager $manager, RdvRepository $repo, Security $security)
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
     * @return Response
     */
    public function showCalendar($year = null, $month = null, RdvRepository $repo, Request $request): Response
    {
        $calendar = new Calendar($year, $month);

        $rdvs = $repo->FindRdvsBetweenByDay($calendar->getFirstMonday(), $calendar->getLastday());

        $rdv = new Rdv;

        $form = $this->createForm(RdvType::class, $rdv);
        $form->handleRequest($request);

        return $this->render("app/rdv/calendar.html.twig", [
            "calendar" => $calendar,
            "rdvs" => $rdvs,
            "form" => $form->createView()
        ]);
    }

    /**
     * Liste des RDVs
     * 
     * @Route("support/{id}/rdv/list", name="rdv_list")
     *
     * @param SupportGroup $supportGroup
     * @param RdvSearch $rdvSearch
     * @param Rdv $rdv
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function listPeople(SupportGroup $supportGroup, RdvSearch $rdvSearch = null, Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $rdvSearch = new RdvSearch;

        $formSearch = $this->createForm(RdvSearchType::class, $rdvSearch);
        $formSearch->handleRequest($request);

        $rdvs =  $this->paginate($paginator, $supportGroup, $rdvSearch, $request);

        $rdv = new Rdv();

        $form = $this->createForm(RdvType::class, $rdv);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->createRdv($supportGroup, $rdv);
        }

        return $this->render("rdv/listRdvs.html.twig", [
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
        // $this->denyAccessUnlessGranted("EDIT", $rdv->getSupportGroup());

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
        // $this->denyAccessUnlessGranted("EDIT", $rdv->getSupportGroup());

        $rdv = $repo->find($rdv->getId());

        return $this->json([
            "code" => 200,
            "action" => "show",
            "data" => [
                "title" => $rdv->getTitle(),
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
        // $this->denyAccessUnlessGranted("EDIT", $rdv->getSupportGroup());

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
        // $this->denyAccessUnlessGranted("EDIT", $rdv->getSupportGroup());

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
    protected function paginate($paginator, $supportGroup, $rdvSearch, $request)
    {
        $rdvs =  $paginator->paginate(
            $this->repo->findAllRdvsQuery($supportGroup->getId(), $rdvSearch),
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
