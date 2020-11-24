<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\UserSearch;
use App\Form\User\UserSearchType;
use App\Repository\UserRepository;
use App\Service\Export\UserExport;
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
     */
    public function listUsers(Request $request, Pagination $pagination): Response
    {
        $search = new UserSearch();

        $form = ($this->createForm(UserSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/user/listUsers.html.twig', [
            'userSearch' => $search,
            'form' => $form->createView(),
            'users' => $pagination->paginate($this->repo->findAllUsersQuery($search), $request) ?? null,
        ]);
    }

    /**
     * Administration des utilisateurs.
     *
     * @Route("admin/users", name="admin_users", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function adminListUsers(Request $request, Pagination $pagination): Response
    {
        $search = new UserSearch();

        $form = ($this->createForm(UserSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/user/adminListUsers.html.twig', [
            'userSearch' => $search,
            'form' => $form->createView(),
            'users' => $pagination->paginate($this->repo->findAllUsersQuery($search, $this->getUser()), $request),
        ]);
    }

    /**
     * Vérifie si le login est déjà utilisé.
     *
     * @Route("/user/username_exists/{username}", name="username_exists", methods="GET")
     */
    public function usernameExists(string $username = ''): Response
    {
        $user = $this->repo->findOneBy(['username' => $username]);

        return $this->json(['response' => $user != null]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(UserSearch $search)
    {
        $users = $this->repo->findUsersToExport($search);

        return (new UserExport())->exportData($users);
    }
}
