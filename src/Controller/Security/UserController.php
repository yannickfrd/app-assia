<?php

namespace App\Controller\Security;

use App\Form\Model\Organization\UserSearch;
use App\Form\Organization\User\UserSearchType;
use App\Repository\Organization\UserRepository;
use App\Service\Export\UserExport;
use App\Service\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Liste des utilisateurs.
     *
     * @Route("/directory/users", name="user_index", methods="GET|POST")
     */
    public function index(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(UserSearchType::class, $search = new UserSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->renderForm('app/organization/user/user_index.html.twig', [
            'form' => $form,
            'users' => $pagination->paginate($this->userRepo->findUsersQuery($search), $request),
        ]);
    }

    /**
     * Administration des utilisateurs.
     *
     * @Route("/admin/users", name="admin_user_index", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminIndex(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(UserSearchType::class, $search = new UserSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/organization/user/admin_user_index.html.twig', [
            'userSearch' => $search,
            'form' => $form->createView(),
            'users' => $pagination->paginate($this->userRepo->findUsersAdminQuery($search, $this->getUser()), $request),
        ]);
    }

    /**
     * Vérifie si le login est déjà utilisé.
     *
     * @Route("/user/username_exists/{username}", name="username_exists", methods="GET")
     */
    public function usernameExists(string $username = ''): JsonResponse
    {
        $user = $this->userRepo->findOneBy(['username' => $username]);

        return $this->json(['response' => null != $user]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(UserSearch $search): Response
    {
        $users = $this->userRepo->findUsersToExport($search);

        return (new UserExport())->exportData($users);
    }
}
