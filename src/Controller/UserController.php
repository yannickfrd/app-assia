<?php

namespace App\Controller;

use App\Export\UserExport;
use App\Form\Model\UserSearch;
use App\Form\User\UserSearchType;
use App\Repository\UserRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $repo;
    private $manager;

    public function __construct(UserRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
    }

    /**
     * Liste des utilisateurs.
     *
     * @Route("directory/users", name="users", methods="GET|POST")
     */
    public function listUsers(Request $request, Pagination $pagination): Response
    {
        // $users = $this->repo->findAll();

        // foreach ($users as $user) {
        //     $user->setPhone1(str_replace(' ', '', $user->getPhone1()));
        // }

        // $this->manager->flush();

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
            'users' => $pagination->paginate($this->repo->findAllUsersQuery($search), $request),
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
    protected function exportData(UserSearch $search)
    {
        $users = $this->repo->findUsersToExport($search);

        return (new UserExport())->exportData($users);
    }
}
