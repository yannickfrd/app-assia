<?php

namespace App\Controller;

use App\Entity\Service;
use App\Service\Pagination;
use App\Export\ServiceExport;
use App\Form\Model\ServiceSearch;
use App\Form\Service\ServiceType;
use App\Repository\UserRepository;
use App\Repository\ServiceRepository;
use App\Form\Service\ServiceSearchType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AccommodationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ServiceController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, ServiceRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des services
     * 
     * @Route("/services", name="services", methods="GET")
     * @param Request $request
     * @param ServiceSearch $serviceSearch
     * @param Pagination $pagination
     * @return Response
     */
    public function listServices(Request $request, ServiceSearch $serviceSearch = null, Pagination $pagination): Response
    {
        $serviceSearch = new ServiceSearch();

        $form = ($this->createForm(ServiceSearchType::class, $serviceSearch))
            ->handleRequest($request);

        if ($serviceSearch->getExport()) {
            return $this->exportData($serviceSearch);
        }

        return $this->render("app/service/listServices.html.twig", [
            "serviceSearch" => $serviceSearch,
            "form" => $form->createView(),
            "services" => $pagination->paginate($this->repo->findAllServicesQuery($serviceSearch), $request) ?? null
        ]);
    }

    /**
     * Nouveau service
     * 
     * @Route("/service/new", name="service_new", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *  @return Response
     */
    public function newService(Service $service = null, Request $request): Response
    {
        $service = new Service();

        $form = ($this->createForm(ServiceType::class, $service))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createService($service);
        }

        return $this->render("app/service/service.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un service
     * 
     * @Route("/service/{id}", name="service_edit", methods="GET|POST")
     * @param integer $id from Service
     * @param UserRepository $repoUser
     * @param AccommodationRepository $repoAccommodation
     * @param Request $request
     * @return Response
     */
    public function editService(int $id, UserRepository $repoUser, AccommodationRepository $repoAccommodation, Request $request): Response
    {
        $service = $this->repo->getFullService($id);

        $this->denyAccessUnlessGranted("VIEW", $service);

        $form = ($this->createForm(ServiceType::class, $service))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted("EDIT", $service);
            $this->updateService($service);
        }

        return $this->render("app/service/service.html.twig", [
            "form" => $form->createView(),
            "users" => $repoUser->findUsersFromService($service),
            "accommodations" => $repoAccommodation->findAccommodationsFromService($service),
            "edit_mode" => true
        ]);
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
     * 
     * @param Service $service
     */
    protected function createService(Service $service)
    {
        $now = new \DateTime();

        $service->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($service);
        $this->manager->flush();

        $this->addFlash("success", "Le service a été créé.");

        return $this->redirectToRoute("service_edit", ["id" => $service->getId()]);
    }

    /**
     * Met à jour un service
     * 
     * @param Service $service
     */
    protected function updateService(Service $service)
    {
        $service->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
    }
}
