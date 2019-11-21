<?php

namespace App\Controller;

use App\Utils\Agree;
use App\Entity\User;
use App\Form\UserType;

use App\Entity\RoleUser;
use App\Entity\Service;

use App\Entity\UserSearch;
use App\Form\RoleUserType;
use App\Form\UserSearchType;
use App\Form\RoleUserGroupType;
use App\Repository\UserRepository;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class UserController extends AbstractController
{
    private $manager;
    private $repo;
    private $request;
    private $security;

    public function __construct(ObjectManager $manager, UserRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Permet de rechercher un utilisateur
     * 
     * @Route("admin/list/users", name="list_users")
     * @Route("/new_support/search/user", name="new_support_search_user")
     * @return Response
     */
    public function listUsers(Request $request, UserSearch $userSearch = null, PaginatorInterface $paginator): Response
    {
        $userSearch = new UserSearch();

        $form = $this->createForm(UserSearchType::class, $userSearch);

        // dd($request);
        $form->handleRequest($request);

        if ($request->query->all()) {

            $users =  $paginator->paginate(
                $this->repo->findAllUsersQuery($userSearch),
                $request->query->getInt("page", 1), // page number
                20 // limit per page
            );
            $users->setPageRange(5);
            $users->setCustomParameters([
                "align" => "right", // alignement de la pagination
            ]);
        }

        return $this->render("app/listUsers.html.twig", [
            // "controller_name" => "UserController",
            "users" => $users ?? null,
            "userSearch" => $userSearch,
            "form" => $form->createView(),
            "current_menu" => "list_users"
        ]);
        // return $this->pagination($userSearch, $request, $form, $paginator);
    }

    /**
     * Permet de rechercher un utilisateur pour l'ajouter dans un service
     * 
     * @Route("/service/{id}/search/user", name="service_search_user")
     * @return Response
     */
    public function serviceSearchUser(Request $request, UserSearch $userSearch = null, Service $service = null, RoleUser $roleUser = null, PaginatorInterface $paginator): Response
    {
        $userSearch = new UserSearch();

        $formRoleUser = null;

        if ($service) {
            $formRoleUser = $this->createFormBuilder($roleUser)
                ->add("role", ChoiceType::class, [
                    "choices" => Choices::getChoices(RoleUser::ROLE),
                ])
                ->getForm();
        }

        $form = $this->createForm(UserSearchType::class, $userSearch);
        $form->handleRequest($request);

        if ($request->query->all()) {
            $users =  $paginator->paginate(
                $this->repo->findAllUsersQuery($userSearch, $search = null),
                $request->query->getInt("page", 1), // page number
                20 // limit per page
            );
            $users->setPageRange(5);
            $users->setCustomParameters([
                "align" => "right", // alignement de la pagination
            ]);
        }

        return $this->render("app/listUsers.html.twig", [
            // "controller_name" => "UserController",
            "service" => $service,
            "users" => $users ?? null,
            "userSearch" => $userSearch,
            "form" => $form->createView(),
            "form_role_user" => $formRoleUser->createView(),
            "current_menu" => "list_users"
        ]);
        // return $this->pagination($userSearch, $request, $service, $form,  $formRoleUser, $paginator);
    }

    public function getchoices($const)
    {
        foreach ($const as $key => $value) {
            $output[$value] = $key;
        }
        return $output;
    }

    /**
     * Vérifie si le login est déjà utilisé
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
     * Crée un nouvel utilisateur
     * 
     * @Route("/user/new", name="user_new", methods="GET|POST")
     * @param User $user
     * @param RoleUser $roleUser
     * @param Service $service
     * @param UserRepository $repo
     * @param Request $request
     * @return Response
     */
    public function newUser(User $user = null, RoleUser $roleUser = null, Service $service = null, UserRepository $repo, Request $request): Response
    {
        $user = new User();
        $roleUser = new RoleUser();
        $service = new Service();

        $form = $this->createForm(RoleUserGroupType::class, $roleUser);
        $form->handleRequest($request);

        $user = $roleUser->getUser();
        $service = $roleUser->getService();

        if ($form->isSubmitted() && $form->isValid()) {

            // Vérifie si l'utilisateur existe déjà dans la base de données
            $userExist = $repo->findOneBy([
                "lastname" => $user->getLastname(),
                "firstname" => $user->getFirstname(),
                "birthdate" => $user->getBirthdate()
            ]);
            // Si l'utilisateur existe déjà, renvoie vers la fiche existante, sinon crée l'utilisateur
            if ($userExist) {
                $this->addFlash(
                    "warning",
                    "Attention : " . $user->getFirstname() . " " . $user->getLastname() . " existe déjà !"
                );
            } else {
                $service->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->security->getUser())
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($this->security->getUser());
                $this->manager->persist($service);

                $roleUser->setHead(true)
                    ->setCreatedAt(new \DateTime())
                    ->setService($service);
                $this->manager->persist($roleUser);

                $user->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->security->getUser())
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($this->security->getUser())
                    ->addRolesUser($roleUser);
                $this->manager->persist($user);

                $this->manager->flush();

                $this->addFlash(
                    "success",
                    $user->getFirstname() . " a été créé" .  Agree::gender($user->getGender()) . ", ainsi que son service."
                );
                return $this->redirectToRoute("service_show", ["id" => $service->getId()]);
            }
        }
        return $this->render("app/user.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }


    /**
     * Crée un nouvel utilisateur dans un service existant
     * 
     * @Route("/service/{id}/user/new", name="service_create_user", methods="GET|POST")
     * @param User $user
     * @param RoleUser $roleUser
     * @param Service $service
     * @param UserRepository $repo
     * @param Request $request
     * @return Response
     */
    public function newUserInGroup(User $user = null, RoleUser $roleUser = null, Service $service, UserRepository $repo, Request $request): Response
    {
        $user = new User();
        $roleUser = new RoleUser();

        $form = $this->createForm(RoleUserType::class, $roleUser);
        $form->handleRequest($request);

        $user = $roleUser->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si l'utilisateur existe déjà dans la base de données
            $userExist = $repo->findOneBy([
                "lastname" => $user->getLastname(),
                "firstname" => $user->getFirstname(),
                "birthdate" => $user->getBirthdate()
            ]);
            // Si l'utilisateur existe déjà, renvoie vers la fiche existante, sinon crée l'utilisateur
            if ($userExist) {
                $this->addFlash(
                    "warning",
                    "Attention : " . $user->getFirstname() . " " . $user->getLastname() . " existe déjà !"
                );
                return $this->redirectToRoute("user_show", ["id" => $userExist->getId()]);
            } else {
                $this->createUser($user, $service, $roleUser);
                return $this->redirectToRoute("service_show", ["id" => $service->getId()]);
            }
        } else {
            return $this->render("app/user.html.twig", [
                "service" => $service,
                "form" => $form->createView(),
                "edit_mode" => false
            ]);
        }
    }

    /**
     * Crée un utilisateur avec son rôle
     *
     * @param User $user
     * @param Service $service
     * @param RoleUser $roleUser
     */
    protected function createUser(User $user, Service $service, RoleUser $roleUser = null)
    {
        $roleUser->setHead(false)
            ->setCreatedAt(new \DateTime())
            ->setService($service);
        $this->manager->persist($roleUser);

        $user->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->security->getUser())
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->security->getUser())
            ->addRolesUser($roleUser);
        $this->manager->persist($user);

        $nbPeople = $service->getRoleUser()->count();
        $service->setNbPeople($nbPeople + 1);

        $this->manager->flush();

        $this->addFlash(
            "success",
            $user->getFirstname() . " a été ajouté" .  Agree::gender($user->getGender()) . " au service."
        );
    }

    /**
     * Modifie un utilisateur
     * 
     * @Route("/service/{id}/user/{user_id}", name="service_user_show", methods="GET|POST")
     * @ParamConverter("user", options={"id" = "user_id"})
     * @param Service $service
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function editUser(Service $service, User $user, Request $request, ValidatorInterface $validator): Response
    {
        $supports = $user->getSupports();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // $nbErrors = count($validator->validate($form));

        if ($form->isSubmitted() && $form->isValid()) {

            // $user->setUpdatedAt(new \DateTime())
            //     ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        } elseif ($form->isSubmitted() && !$form->isValid()) {

            $this->addFlash("danger", "Les informations saisies sont invalides.");
        }

        return $this->render("app/user.html.twig", [
            "service" => $service,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Met à jour les informations d'un utilisateur via Ajax
     * 
     * @Route("/user/update-{id}", name="update_user", methods="GET|POST")
     * @param User $user
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function updateUser(User $user, Request $request, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $now = new \DateTime();

        if ($form->isSubmitted() && $form->isValid()) {
            // $user->setUpdatedAt($now)
            //     ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $alert = "success";
            $msg[] = "Les modifications ont été enregistrées.";
        } else {
            $alert = "danger";
            $errors = $validator->validate($form);
            foreach ($errors as $error) {
                $msg[] = $error->getMessage();
            }
        }
        return $this->json([
            "code" => 200,
            "alert" => $alert,
            "msg" => $msg,
            "user" => $this->getUser()->getUsername(),
            "date" => date_format($now, "d/m/Y à H:i")
        ], 200);
    }

    /**
     * Voir la fiche individuelle
     * 
     * @Route("/user/{id}", name="user_show", methods="GET|POST")
     *  @return Response
     */
    public function userShow(User $user, RoleUser $roleUser = null, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $user->setUpdatedAt(new \DateTime())
            //     ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        }

        return $this->render("app/user.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }


    // Met en place la pagination du tableau et affiche le rendu
    protected function pagination($userSearch, $request, $service, $form, $formRoleUser = null, $paginator)
    { }

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
