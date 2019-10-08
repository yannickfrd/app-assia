<?php

namespace App\Controller;

use App\Entity\Person;

use App\Form\PersonType;

use App\Repository\PersonRepository;

use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PersonController extends AbstractController
{
    private $manager;
    private $repo;
    private $security;
    private $session;

    public function __construct(ObjectManager $manager, PersonRepository $repo, Security $security, SessionInterface $session)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->session = $session;
        $this->security = $security;
    }

    /**
     * @Route("/list/people", name="list_people")
     * @return Response
     */
    public function listPeople(PaginatorInterface $paginator, Request $request): Response
    {
        $people =  $paginator->paginate(
            $this->repo->findAllPeopleQuery(),
            $request->query->getInt("page", 1), /*page number*/
            20 /*limit per page*/
        );

        return $this->render("app/listPeople.html.twig", [
            "controller_name" => "PersonController",
            "people" => $people,
            "current_menu" => "list_people",
            "align" => "right",
        ]);

        
    }

    /**
     * Accorde en fonction du sexe de la personne (féminin, masculin)
     * @return String
     */
    private function gender($gender): String
    {
        if($gender == 1) {
            return "e";
        } else {
            return "";
        }
    }
}