<?php

namespace App\Controller;

use App\Entity\Pole;
use App\Form\PoleType;
use App\Entity\PoleSearch;
use App\Form\PoleSearchType;
use App\Repository\PoleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PoleController extends AbstractController
{
    private $manager;
    private $repo;
    private $request;
    private $security;

    public function __construct(ObjectManager $manager, PoleRepository $repo, Security $security)
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
        $search = $request->query->get("search");

        $poles =  $paginator->paginate(
            $this->repo->findAllPolesQuery(),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $poles->setPageRange(5);
        $poles->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/listpoles.html.twig", [
            "poles" => $poles ?? null,
            "current_menu" => "list_poles"
        ]);
    }

    /**
     * Voir la fiche du pôle
     * 
     * @Route("/pole/{id}", name="pole_show", methods="GET|POST")
     *  @return Response
     */
    public function poleShow(Pole $pole, Request $request): Response
    {
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
