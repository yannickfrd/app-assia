<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization\Service;
use App\Form\Model\Organization\ServiceSearch;
use App\Form\Organization\Service\ServiceSearchType;
use App\Form\Organization\Service\ServiceType;
use App\Form\Organization\Tag\ServiceTagType;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\Organization\TagRepository;
use App\Repository\Organization\UserRepository;
use App\Service\Export\ServiceExport;
use App\Service\Pagination;
use App\Service\Service\ServiceManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ServiceController extends AbstractController
{
    private $serviceRepo;
    private $em;

    public function __construct(ServiceRepository $serviceRepo, EntityManagerInterface $em)
    {
        $this->serviceRepo = $serviceRepo;
        $this->em = $em;
    }

    /**
     * @Route("/services", name="service_index", methods="GET")
     */
    public function index(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(ServiceSearchType::class, $search = new ServiceSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/organization/service/service_index.html.twig', [
            'serviceSearch' => $search,
            'form' => $form->createView(),
            'services' => $pagination->paginate($this->serviceRepo->findServicesQuery($search, $this->getUser()), $request) ?? null,
        ]);
    }

    /**
     * @Route("/service/new", name="service_new", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function new(Request $request, ServiceManager $serviceManager): Response
    {
        $form = $this->createForm(ServiceType::class, $service = $serviceManager->createService())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($service);
            $this->em->flush();

            $this->addFlash('success', 'Le service est créé.');

            return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
        }

        return $this->render('app/organization/service/service.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/service/{id}", name="service_edit", methods="GET|POST")
     *
     * @param int $id from Service
     */
    public function edit(
        int $id,
        Request $request,
        SubServiceRepository $subServiceRepo,
        UserRepository $userRepo,
        PlaceRepository $placeRepo,
        TagRepository $tagRepo,
        ServiceManager $serviceManager
    ): Response {
        $service = $serviceManager->getFullService($id);

        $this->denyAccessUnlessGranted('VIEW', $service);

        $formTags = $this->createForm(ServiceTagType::class, $service);

        $form = $this->createForm(ServiceType::class, $service)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $service);

            $this->em->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        $places = $placeRepo->findPlacesOfService($service);

        $nbPlaces = 0;
        foreach ($places as $place) {
            $nbPlaces += $place->getNbPlaces();
        }

        $tags = $tagRepo->findTagByService($service);

        return $this->render('app/organization/service/service.html.twig', [
            'form' => $form->createView(),
            'sub_services' => $subServiceRepo->findSubServicesOfService($service),
            'users' => $userRepo->findUsersOfService($service),
            'places' => $places,
            'nb_places' => $nbPlaces,
            'form_tags' => $formTags->createView(),
            'service_tags' => $tags,
        ]);
    }

    /**
     * @Route("/admin/service/{id}/disable", name="service_disable", methods="GET")
     */
    public function disable(Service $service): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $service);

        if ($service->getDisabledAt()) {
            $service->setDisabledAt(null);
            $this->addFlash('success', 'Le service "'.$service->getName().'" est ré-activé.');
        } else {
            $service->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le service "'.$service->getName().'" est désactivé.');
        }

        $this->em->flush();

        return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(ServiceSearch $search): Response
    {
        $services = $this->serviceRepo->findServicesToExport($search);

        return (new ServiceExport())->exportData($services);
    }
}
