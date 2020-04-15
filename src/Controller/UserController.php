<?php

namespace App\Controller;

use App\Export\UserExport;
use App\Form\Model\UserSearch;
use App\Form\User\UserSearchType;
use App\Repository\UserRepository;
use App\Service\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $repo;

    public function __construct(UserRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Liste des utilisateurs.
     *
     * @Route("directory/users", name="users", methods="GET|POST")
     *
     * @param UserSearch $userSearch
     */
    public function listUsers(Request $request, UserSearch $userSearch = null, Pagination $pagination): Response
    {
        $userSearch = new UserSearch();

        $form = ($this->createForm(UserSearchType::class, $userSearch))
            ->handleRequest($request);

        if ($userSearch->getExport()) {
            return $this->exportData($userSearch);
        }

        return $this->render('app/user/listUsers.html.twig', [
            'userSearch' => $userSearch,
            'form' => $form->createView(),
            'users' => $pagination->paginate($this->repo->findAllUsersQuery($userSearch, ), $request) ?? null,
            'disabled_users' => false,
        ]);
    }

    /**
     * Administration des utilisateurs.
     *
     * @Route("admin/users", name="admin_users", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @param UserSearch $userSearch
     */
    public function adminListUsers(Request $request, UserSearch $userSearch = null, Pagination $pagination): Response
    {
        $userSearch = new UserSearch();

        $form = ($this->createForm(UserSearchType::class, $userSearch))
            ->handleRequest($request);

        if ($userSearch->getExport()) {
            return $this->exportData($userSearch);
        }

        return $this->render('app/user/adminListUsers.html.twig', [
            'userSearch' => $userSearch,
            'form' => $form->createView(),
            'users' => $pagination->paginate($this->repo->findAllUsersQuery($userSearch), $request),
            'disabled_users' => true,
        ]);
    }

    /**
     * Vérifie si le login est déjà utilisé.
     *
     * @Route("/user/username_exists", name="username_exists", methods="GET")
     */
    public function usernameExists(Request $request): Response
    {
        $user = $this->repo->findOneBy(['username' => $request->query->get('value')]);

        return $this->json([
            'response' => $user ? true : false,
        ], 200);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(UserSearch $userSearch)
    {
        $users = $this->repo->findUsersToExport($userSearch);

        return (new UserExport())->exportData($users);
    }
}
