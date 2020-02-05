<?php

namespace App\Controller;

use App\Entity\Service;
use App\Form\Model\ServiceSearch;
use App\Form\Service\ServiceType;
use App\Form\Service\ServiceSearchType;
use App\Export\ServiceExport;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ServiceController extends AbstractController
{
    private $manager;
    private $currentUser;
    private $repo;

    public function __construct(EntityManagerInterface $manager, Security $security, ServiceRepository $repo)
    {
        $this->manager = $manager;
        $this->currentUser = $security->getUser();
        $this->repo = $repo;
    }

    /**
     * Permet de rechercher un service
     * 
     * @Route("/services", name="services")
     * @return Response
     */
    public function listService(Request $request, ServiceSearch $serviceSearch = null, PaginatorInterface $paginator): Response
    {
        $serviceSearch = new ServiceSearch();

        $form = $this->createForm(ServiceSearchType::class, $serviceSearch);
        $form->handleRequest($request);

        if ($serviceSearch->getExport()) {
            $this->exportData($serviceSearch);
        }

        $services = $this->paginate($paginator, $serviceSearch, $request);

        return $this->render("app/listServices.html.twig", [
            "services" => $services ?? null,
            "serviceSearch" => $serviceSearch,
            "form" => $form->createView(),
            "current_menu" => "services"
        ]);
    }

    /**
     * Nouveau service
     * 
     * @Route("/service/new", name="service_new", methods="GET|POST")
     *  @return Response
     */
    public function newService(Service $service = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $service = new Service();

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createService($service);
        }

        return $this->render("app/service.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un service
     * 
     * @Route("/service/{id}", name="service_edit", methods="GET|POST")
     *  @return Response
     */
    public function editService(Service $service, Request $request): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $service);

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateService($service);
        }

        return $this->render("app/service.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Pagine
     *
     * @param PaginatorInterface $paginator
     * @param ServiceSearch $serviceSearch
     * @param Request $request
     * @return void
     */
    protected function paginate(PaginatorInterface $paginator, ServiceSearch $serviceSearch, Request $request)
    {
        $services =  $paginator->paginate(
            $this->repo->findAllServicesQuery($serviceSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $services->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);
        return $services;
    }

    /**
     * Exporte les données
     *
     * @param ServiceSearch $serviceSearch
     */
    protected function exportData(ServiceSearch $serviceSearch)
    {
        $services = $this->repo->findServicesToExport($serviceSearch);
        $export = new ServiceExport();
        return $export->exportData($services);
    }

    /**
     * Crée un service
     * @param Service $service
     */
    protected function createService(Service $service)
    {
        $now = new \DateTime();

        $service->setCreatedAt($now)
            ->setCreatedBy($this->currentUser)
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->currentUser);

        $this->manager->persist($service);
        $this->manager->flush();

        $this->addFlash("success", "Le service a été créé.");

        return $this->redirectToRoute("service_edit", ["id" => $service->getId()]);
    }

    /**
     * Met à jour un service
     * @param Service $service
     */
    protected function updateService(Service $service)
    {
        $service->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->currentUser);

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
    }
}
