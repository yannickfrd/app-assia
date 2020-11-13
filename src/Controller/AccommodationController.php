<?php

namespace App\Controller;

use App\Entity\Accommodation;
use App\Entity\Service;
use App\Form\Accommodation\AccommodationSearchType;
use App\Form\Accommodation\AccommodationType;
use App\Form\Model\AccommodationSearch;
use App\Repository\AccommodationGroupRepository;
use App\Repository\AccommodationRepository;
use App\Security\CurrentUserService;
use App\Service\Export\AccommodationExport;
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
     * @Route("/accommodations", name="accommodations", methods="GET|POST")
     */
    public function listAccommodations(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $search = new AccommodationSearch();

        $form = ($this->createForm(AccommodationSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/accommodation/listAccommodations.html.twig', [
            'accommodationSearch' => $search,
            'form' => $form->createView(),
            'accommodations' => $pagination->paginate($this->repo->findAllAccommodationsQuery($search, $currentUser), $request) ?? null,
        ]);
    }

    /**
     * Nouveau groupe de places.
     *
     * @Route("/admin/service/{id}/accommodation/new", name="service_accommodation_new", methods="GET|POST")
     */
    public function newAccommodation(Service $service, Accommodation $accommodation = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $service);

        $accommodation = (new Accommodation())->setService($service);

        $form = ($this->createForm(AccommodationType::class, $accommodation))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($accommodation);
            $this->manager->flush();

            $this->addFlash('success', 'Le groupe de places est créé.');

            return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Une erreur s'est produite.");
        }

        return $this->render('app/accommodation/accommodation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un groupe de places.
     *
     * @Route("/accommodation/{id}", name="accommodation_edit", methods="GET|POST")
     */
    public function editAccommodation(Accommodation $accommodation, Request $request, AccommodationGroupRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $accommodation);

        $form = ($this->createForm(AccommodationType::class, $accommodation))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $accommodation->getService());

            $this->manager->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        return $this->render('app/accommodation/accommodation.html.twig', [
            'form' => $form->createView(),
            'accommodations_group' => $repo->findAllFromAccommodation($accommodation),
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

        $this->addFlash('warning', 'Le groupe de places est supprimé.');

        return $this->redirectToRoute('service_edit', ['id' => $accommodation->getService()->getId()]);
    }

    /**
     * Désactive ou réactive le accommodation.
     *
     * @Route("admin/accommodation/{id}/disable", name="admin_accommodation_disable", methods="GET")
     */
    public function disableAccommodation(Accommodation $accommodation): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $accommodation);

        if ($accommodation->getDisabledAt()) {
            $accommodation->setDisabledAt(null);
            $this->addFlash('success', 'Le groupe de place "'.$accommodation->getName().'" est réactivé.');
        } else {
            $accommodation->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le groupe de place "'.$accommodation->getName().'" est désactivé.');
        }

        $this->manager->flush();

        return $this->redirectToRoute('accommodation_edit', ['id' => $accommodation->getId()]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(AccommodationSearch $search)
    {
        $accommodations = $this->repo->findAccommodationsToExport($search);

        if (!$accommodations) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new AccommodationExport())->exportData($accommodations);
    }
}
