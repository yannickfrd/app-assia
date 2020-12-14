<?php

namespace App\Controller\Organization;

use App\Service\Pagination;
use App\Entity\Organization\Pole;
use App\Form\Organization\Pole\PoleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\Organization\PoleRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PoleController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, PoleRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des pôles.
     *
     * @Route("/poles", name="poles", methods="GET")
     */
    public function listPole(Request $request, Pagination $pagination): Response
    {
        return $this->render('app/organization/pole/listPoles.html.twig', [
            'poles' => $pagination->paginate($this->repo->findAllPolesQuery(), $request) ?? null,
        ]);
    }

    /**
     * Nouveau pôle.
     *
     * @Route("/pole/new", name="pole_new", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function newPole(Pole $pole = null, Request $request): Response
    {
        $pole = new Pole();

        $form = ($this->createForm(PoleType::class, $pole))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($pole);
            $this->manager->flush();

            $this->addFlash('success', 'Le pôle est créé.');

            return $this->redirectToRoute('pole_edit', ['id' => $pole->getId()]);
        }

        return $this->render('app/organization/pole/pole.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un pôle.
     *
     * @Route("/pole/{id}", name="pole_edit", methods="GET|POST")
     */
    public function editPole(Pole $pole, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $pole);

        $form = ($this->createForm(PoleType::class, $pole))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        return $this->render('app/organization/pole/pole.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
