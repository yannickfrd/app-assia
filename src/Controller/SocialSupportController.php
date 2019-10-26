<?php

namespace App\Controller;

use App\Utils\Agree;

use App\Entity\Person;

use App\Entity\RolePerson;
use App\Entity\GroupPeople;

use App\Form\GroupPeopleType;

use App\Entity\GroupPeopleSearch;
use App\Form\GroupPeopleSearchType;

use App\Repository\RolePersonRepository;
use App\Repository\GroupPeopleRepository;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SocialSupportController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(ObjectManager $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    /**
     * @Route("/list/social_supports", name="list_social_supports")
     * @return Response
     */
    public function listSocialSupports(RolePersonRepository $repo, GroupPeopleSearch $groupPeopleSearch = null, Request $request, PaginatorInterface $paginator): Response
    {
        $groupPeopleSearch = new GroupPeopleSearch();

        $form = $this->createForm(GroupPeopleSearchType::class, $groupPeopleSearch);
        $form->handleRequest($request);

        $rolePeople =  $paginator->paginate(
            $repo->findAllSocialSupports($groupPeopleSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $rolePeople->setPageRange(5);
        $rolePeople->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/listSocialSupports.html.twig", [
            "controller_name" => "listSocialSupports",
            "role_people" => $rolePeople,
            "form" => $form->createView(),
            "current_menu" => "social_supports"
        ]);
    }
}
