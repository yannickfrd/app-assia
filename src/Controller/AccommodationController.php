<?php

namespace App\Controller;

use App\Entity\Accommodation;
use App\Entity\Service;
use App\Export\AccommodationExport;
use App\Form\Accommodation\AccommodationSearchType;
use App\Form\Accommodation\AccommodationType;
use App\Form\Model\AccommodationSearch;
use App\Repository\AccommodationRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccommodationController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, AccommodationRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Affiche la liste des groupes de places.
     *
     * @Route("/admin/accommodations", name="admin_accommodations", methods="GET|POST")
     */
    public function listAccommodations(AccommodationSearch $accommodationSearch, Request $request, Pagination $pagination): Response
    {
        $accommodationSearch = new AccommodationSearch();

        $form = ($this->createForm(AccommodationSearchType::class, $accommodationSearch))
            ->handleRequest($request);

        if ($accommodationSearch->getExport()) {
            return $this->exportData($accommodationSearch);
        }

        return $this->render('app/accommodation/listAccommodations.html.twig', [
            'accommodationSearch' => $accommodationSearch,
            'form' => $form->createView(),
            'accommodations' => $pagination->paginate($this->repo->findAllAccommodationsQuery($accommodationSearch), $request) ?? null,
        ]);
    }

    /**
     * Nouveau groupe de places.
     *
     * @Route("/admin/service/{id}/accommodation/new", name="service_accommodation_new", methods="GET|POST")
     *
     * @param Accommodation $accommodation
     */
    public function newAccommodation(Service $service, Accommodation $accommodation = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $service);

        $accommodation = (new Accommodation())->setService($service);

        $form = ($this->createForm(AccommodationType::class, $accommodation))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createAccommodation($accommodation, $service);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Une erreur s'est produite.");
        }

        return $this->render('app/accommodation/accommodation.html.twig', [
            'form' => $form->createView(),
            'edit_mode' => false,
        ]);
    }

    /**
     * Modification d'un groupe de places.
     *
     * @Route("/accommodation/{id}", name="accommodation_edit", methods="GET|POST")
     * @IsGranted("VIEW", subject="accommodation")
     */
    public function editAccommodation(Accommodation $accommodation, Request $request): Response
    {
        $form = ($this->createForm(AccommodationType::class, $accommodation))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $accommodation->getService());

            return $this->updateAccommodation($accommodation);
        }

        return $this->render('app/accommodation/accommodation.html.twig', [
            'form' => $form->createView(),
            'edit_mode' => true,
        ]);
    }

    /**
     * Supprime le groupe de places.
     *
     * @Route("admin/accommodation/{id}/delete", name="admin_accommodation_delete", methods="GET")
     * @IsGranted("DELETE", subject="accommodation")
     */
    public function deleteAccommodation(Accommodation $accommodation): Response
    {
        $this->manager->remove($accommodation);
        $this->manager->flush();

        $this->addFlash('danger', 'Le groupe de places a été supprimé.');

        return $this->redirectToRoute('service_edit', ['id' => $accommodation->getService()->getId()]);
    }

    /**
     * Crée un groupe de places.
     */
    protected function createAccommodation(Accommodation $accommodation, Service $service)
    {
        $now = new \DateTime();

        $accommodation->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($accommodation);
        $this->manager->flush();

        $this->addFlash('success', 'Le groupe de places a été créé.');

        return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
    }

    /**
     * Met à jour un groupe de place.
     */
    protected function updateAccommodation(Accommodation $accommodation)
    {
        $accommodation->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash('success', 'Les modifications ont été enregistrées.');

        return $this->redirectToRoute('service_edit', ['id' => $accommodation->getService()->getId()]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(AccommodationSearch $accommodationSearch)
    {
        $accommodations = $this->repo->findAccommodationsToExport($accommodationSearch);

        if (!$accommodations) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        $export = new AccommodationExport();

        return $export->exportData($accommodations);
    }
}
