<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\ServiceType;
use App\Entity\ServiceSearch;
use App\Form\ServiceSearchType;
use App\Repository\ServiceRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ServiceController extends AbstractController
{
    private $manager;
    private $repo;
    private $request;
    private $security;

    public function __construct(ObjectManager $manager, ServiceRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Permet de rechercher un service
     * 
     * @Route("/list/services", name="list_services")
     * @return Response
     */
    public function listService(Request $request, ServiceSearch $serviceSearch = null, PaginatorInterface $paginator): Response
    {
        $serviceSearch = new ServiceSearch();

        $form = $this->createForm(ServiceSearchType::class, $serviceSearch);
        $form->handleRequest($request);

        $search = $request->query->get("search");


        if ($request->query->all()) {
            $services =  $paginator->paginate(
                $this->repo->findAllServicesQuery($serviceSearch, $search),
                $request->query->getInt("page", 1), // page number
                20 // limit per page
            );
            $services->setPageRange(5);
            $services->setCustomParameters([
                "align" => "right", // alignement de la pagination
            ]);
        }

        return $this->render("app/listservices.html.twig", [
            "services" => $services ?? null,
            "serviceSearch" => $serviceSearch,
            "form" => $form->createView(),
            "current_menu" => "list_services"
        ]);
    }

    /**
     * Voir la fiche individuelle
     * 
     * @Route("/service/{id}", name="service_show", methods="GET|POST")
     *  @return Response
     */
    public function serviceShow(Service $service, Request $request): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $service->setUpdatedAt(new \DateTime())
            //     ->setUpdatedBy($this->security->getservice());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        }

        return $this->render("app/service.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }
}
