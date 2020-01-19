<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserSearch;

use App\Export\UserExport;
use App\Form\User\UserType;

use App\Form\User\UserSearchType;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private $manager;
    private $repo;
    private $request;
    private $security;

    public function __construct(EntityManagerInterface $manager, UserRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Permet de rechercher un utilisateur
     * 
     * @Route("directory/users", name="users")
     * @Route("/new_support/search/user", name="new_support_search_user")
     * @return Response
     */
    public function listUsers(Request $request, UserSearch $userSearch = null, PaginatorInterface $paginator): Response
    {
        $userSearch = new UserSearch();

        $form = $this->createForm(UserSearchType::class, $userSearch);

        $form->handleRequest($request);

        if ($userSearch->getExport()) {
            $users = $this->repo->findUsersToExport($userSearch);
            $export = new UserExport();
            return $export->exportData($users);
        }

        if ($request->query->all()) {

            $users =  $paginator->paginate(
                $this->repo->findAllUsersQuery($userSearch),
                $request->query->getInt("page", 1), // page number
                20 // limit per page
            );
            // $users->setPageRange(5);
            $users->setCustomParameters([
                "align" => "right", // alignement de la pagination
            ]);
        }

        return $this->render("app/listUsers.html.twig", [
            "users" => $users ?? null,
            "userSearch" => $userSearch,
            "form" => $form->createView()
        ]);
        // return $this->pagination($userSearch, $request, $form, $paginator);
    }

    /**
     * Vérifie si le login est déjà utilisé
     * 
     * @Route("/user/check_username", name="user_check_username", methods="GET")
     * @param Request $request
     * @return Response
     */
    public function checkUsername(Request $request): Response
    {
        $user = $this->repo->findOneBy(["username" => $request->query->get("value")]);

        if ($user) {
            $exists = true;
        } else {
            $exists = false;
        }
        return $this->json(["response" => $exists], 200);
    }

    /**
     * Voir la fiche Utilisateur
     * 
     * @Route("/user/{id}", name="user_show", methods="GET|POST")
     *  @return Response
     */
    public function showUser(User $user, Request $request): Response
    {


        $this->denyAccessUnlessGranted("EDIT", $user);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        }

        return $this->render("app/user.html.twig", [
            "form" => $form->createView(),
        ]);
    }

    /**
     * Permet de trouver les utilisateurs par le mode de recherche instannée
     *
     * @Route("/search/user", name="search_user")
     * @param User $user
     * @param Request $request
     * @param UserRepository $repo
     * @return Response
     */
    public function searchUser(Request $request): Response
    {
        if ($request->query->get("search")) {
            $search = $request->query->get("search");
        } else {
            $search = null;
        }

        $users = $this->repo->findPeopleByResearch($search);
        $nbResults = count($users);

        if ($nbResults) {
            foreach ($users as $user) {
                $results[] = [
                    "id" => $user->getId(),
                    "lastname" => $user->getLastname(),
                    "firstname" => $user->getFirstname()
                ];
            }
            return $this->json([
                "nb_results" => $nbResults,
                "results" => $results
            ], 200);
        } else {
            return $this->json([
                "nb_results" => $nbResults,
                "results" => "Aucun résultat."
            ], 200);
        }
    }
}
