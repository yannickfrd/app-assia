<?php

namespace App\Controller;

use App\Entity\Pole;

use App\Form\Pole\PoleType;

use App\Repository\PoleRepository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PoleController extends AbstractController
{
    private $manager;
    private $repo;
    private $request;
    private $security;

    public function __construct(EntityManagerInterface $manager, PoleRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Permet de rechercher un pôle
     * 
     * @Route("/list/poles", name="list_poles")
     * @return Response
     */
    public function listPole(Request $request, PaginatorInterface $paginator): Response
    {
        $poles =  $paginator->paginate(
            $this->repo->findAllPolesQuery(),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $poles->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/listPoles.html.twig", [
            "poles" => $poles ?? null
        ]);
    }

    /**
     * Créer un pôle
     * 
     * @Route("/pole/new", name="pole_new", methods="GET|POST")
     *  @return Response
     */
    public function createPole(Pole $pole = null, Request $request): Response
    {
        $pole = new Pole();

        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $form = $this->createForm(PoleType::class, $pole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->security->getUser();

            $pole->setCreatedAt(new \DateTime())
                // ->setCreatedBy($user)
                // ->setUpdatedBy($user)
                ->setUpdatedAt(new \DateTime());

            $this->manager->persist($pole);
            $this->manager->flush();

            $this->addFlash("success", "Le pôle a été créé.");

            return $this->redirectToRoute("pole_edit", ["id" => $pole->getId()]);
        }

        return $this->render("app/pole.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Editer la fiche du pôle
     * 
     * @Route("/pole/{id}", name="pole_edit", methods="GET|POST")
     *  @return Response
     */
    public function editPole(Pole $pole, Request $request): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $pole);

        $form = $this->createForm(PoleType::class, $pole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $pole->setUpdatedAt(new \DateTime())
            //     ->setUpdatedBy($this->security->getpole());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        }

        return $this->render("app/pole.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }
}
