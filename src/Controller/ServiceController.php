<?php

namespace App\Controller;

use App\Entity\Service;

use App\Form\Model\ServiceSearch;
use App\Form\Service\ServiceType;

use App\Export\ServiceExport;

use App\Repository\ServiceRepository;

use App\Form\Service\ServiceSearchType;
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
    private $repo;
    private $request;
    private $security;

    public function __construct(EntityManagerInterface $manager, ServiceRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
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
            $services = $this->repo->findServicesToExport($serviceSearch);
            $export = new ServiceExport();
            return $export->exportData($services);
        }

        $services =  $paginator->paginate(
            $this->repo->findAllServicesQuery($serviceSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        // $services->setPageRange(5);
        $services->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/listServices.html.twig", [
            "services" => $services ?? null,
            "serviceSearch" => $serviceSearch,
            "form" => $form->createView(),
            "current_menu" => "services"
        ]);
    }

    /**
     * Créer un service
     * 
     * @Route("/service/new", name="service_new", methods="GET|POST")
     *  @return Response
     */
    public function createService(Service $service = null, Request $request): Response
    {
        $service = new Service();

        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->security->getUser();

            $service->setCreatedAt(new \DateTime())
                // ->setCreatedBy($user)
                // ->setUpdatedBy($user)
                ->setUpdatedAt(new \DateTime());

            $this->manager->persist($service);
            $this->manager->flush();

            $this->addFlash("success", "Le service a été créé.");

            return $this->redirectToRoute("service_edit", ["id" => $service->getId()]);
        }

        return $this->render("app/service.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Editer la fiche du service
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

            $service->setUpdatedAt(new \DateTime());
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
