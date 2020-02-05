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
    private $currentUser;
    private $repo;

    public function __construct(EntityManagerInterface $manager, Security $security, PoleRepository $repo)
    {
        $this->manager = $manager;
        $this->currentUser = $security->getUser();
        $this->repo = $repo;
    }

    /**
     * Rechercher un pôle
     * 
     * @Route("/poles", name="poles")
     * @return Response
     */
    public function listPole(Request $request, PaginatorInterface $paginator): Response
    {
        $poles =  $paginator->paginate(
            $this->repo->findAllPolesQuery()
        );

        return $this->render("app/admin/listPoles.html.twig", [
            "poles" => $poles ?? null
        ]);
    }

    /**
     * Nouveau pôle
     * 
     * @Route("/pole/new", name="pole_new", methods="GET|POST")
     *  @return Response
     */
    public function newPole(Pole $pole = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $pole = new Pole();

        $form = $this->createForm(PoleType::class, $pole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createPole($pole);
        }

        return $this->render("app/admin/pole.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un pôle
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
            $this->updatePole($pole);
        }

        return $this->render("app/admin/pole.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Crée un pôle
     *
     * @param Pole $pole
     */
    protected function createPole(Pole $pole)
    {

        $now = new \DateTime();

        $pole->setCreatedAt($now)
            ->setCreatedBy($this->currentUser)
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->currentUser);

        $this->manager->persist($pole);
        $this->manager->flush();

        $this->addFlash("success", "Le pôle a été créé.");

        return $this->redirectToRoute("pole_edit", ["id" => $pole->getId()]);
    }

    /**
     * Met à jour un pôle
     *
     * @param Pole $pole
     */
    protected function updatePole(Pole $pole)
    {
        $pole->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->currentUser);

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
    }
}
