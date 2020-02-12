<?php

namespace App\Controller;

use App\Export\UserExport;
use App\Form\Model\UserSearch;
use App\Form\User\UserSearchType;
use App\Repository\UserRepository;
use App\Service\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Liste des utilisateurs
     * 
     * @Route("directory/users", name="users")
     * @param Request $request
     * @param UserSearch $userSearch
     * @param Pagination $pagination
     * @return Response
     */
    public function listUsers(Request $request, UserSearch $userSearch = null, Pagination $pagination): Response
    {
        $userSearch = new UserSearch();

        $form = $this->createForm(UserSearchType::class, $userSearch);
        $form->handleRequest($request);

        if ($userSearch->getExport()) {
            return $this->exportData($userSearch);
        }

        $users = $pagination->paginate($this->repo->findAllUsersQuery($userSearch), $request);

        return $this->render("app/user/listUsers.html.twig", [
            "userSearch" => $userSearch,
            "form" => $form->createView(),
            "users" => $users ?? null
        ]);
    }

    /**
     * Administration des utilisateurs
     * 
     * @Route("admin/users", name="admin_users")
     * @param Request $request
     * @param UserSearch $userSearch
     * @param Pagination $pagination
     * @return Response
     */
    public function adminListUsers(Request $request, UserSearch $userSearch = null, Pagination $pagination): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $userSearch = new UserSearch();

        $form = $this->createForm(UserSearchType::class, $userSearch);
        $form->handleRequest($request);

        if ($userSearch->getExport()) {
            return $this->exportData($userSearch);
        }

        $users = $pagination->paginate($this->repo->findAllUsersQuery($userSearch), $request);

        return $this->render("app/user/adminListUsers.html.twig", [
            "userSearch" => $userSearch,
            "form" => $form->createView(),
            "users" => $users
        ]);
    }

    /**
     * Vérifie si le login est déjà utilisé
     * 
     * @Route("/user/username_exists", name="username_exists", methods="GET")
     * @param Request $request
     * @return Response
     */
    public function usernameExists(Request $request): Response
    {
        $user = $this->repo->findOneBy(["username" => $request->query->get("value")]);

        return $this->json([
            "response" => $user ? true : false
        ], 200);
    }

    /**
     * Exporte les données
     * @param UserSearch $userSearch
     */
    protected function exportData(UserSearch $userSearch)
    {
        $users = $this->repo->findUsersToExport($userSearch);
        $export = new UserExport();
        return $export->exportData($users);
    }
}
