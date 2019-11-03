<?php

namespace App\Controller;

use App\Utils\Agree;

use App\Entity\Person;

use App\Entity\RolePerson;
use App\Entity\GroupPeople;

use App\Form\GroupPeopleType;

use App\Entity\SocialSupportGrp;
use App\Entity\GroupPeopleSearch;

use App\Entity\SocialSupportPers;
use App\Form\SocialSupportGrpType;
use App\Form\GroupPeopleSearchType;

use App\Repository\RolePersonRepository;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\SocialSupportGrpRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
     * @param RolePersonRepository $repo
     * @param GroupPeopleSearch $groupPeopleSearch
     * @param Request $request
     * @param PaginatorInterface $paginator
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

    /**
     * Crée un nouveau suivi social
     * 
     * @Route("/group/{id}/social_support/new", name="social_support_new", methods="GET|POST")
     * @param GroupPeople $groupPeople
     * @param SocialSupportGrpRepository $repo
     * @param Request $request
     * @return Response
     */
    public function newSocialSupport(GroupPeople $groupPeople, SocialSupportGrpRepository $repo, Request $request): Response
    {
        $socialSupportGrp = new SocialSupportGrp();

        $form = $this->createForm(SocialSupportGrpType::class, $socialSupportGrp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si un suivi social est déjà en cours
            $activeSocialSupport = $repo->findBy([
                "groupPeople" => $groupPeople,
                "status" => 2,
                "service" => $socialSupportGrp->getService()
            ]);

            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if (!$activeSocialSupport) {

                $user = $this->security->getUser();

                $socialSupportGrp->setGroupPeople($groupPeople)
                    // ->setService($service)
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($user)
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($user);

                $this->manager->persist($socialSupportGrp);

                // Créé un suivi social individuel pour chaque personne du groupe
                foreach ($groupPeople->getRolePerson() as $rolePerson) {
                    $socialSupportPers = new SocialSupportPers();

                    $socialSupportPers->setSocialSupportGrp($socialSupportGrp)
                        ->setPerson($rolePerson->getPerson())
                        ->setStartDate($socialSupportGrp->getStartDate())
                        ->setEndDate($socialSupportGrp->getEndDate())
                        ->setStatus($socialSupportGrp->getStatus())
                        ->setCreatedAt(new \DateTime())
                        ->setUpdatedAt(new \DateTime());
                    $this->manager->persist($socialSupportPers);
                };

                $this->manager->flush();

                $this->addFlash(
                    "success",
                    "Le suivi social a été créé."
                );
                return $this->redirectToRoute("social_support_show", [
                    "id" => $groupPeople->getId(),
                    "social_support_id" => $socialSupportGrp->getId()
                ]);
            } else {
                $this->addFlash(
                    "danger",
                    "Attention, un suivi social est déjà en cours pour ce groupe."
                );
            }
        }
        return $this->render("app/socialSupport.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Voir un suvi social
     * 
     * @Route("/group/{id}/social_support/{social_support_id}", name="social_support_show", methods="GET|POST")
     * @ParamConverter("socialSupportGrp", options={"id" = "social_support_id"})
     * @param GroupPeople $groupPeople
     * @param SocialSupportGrp $socialSupportGrp
     * @param SocialSupportPers $socialSupportPers
     * @param SocialSupportGrpRepository $repo
     * @param Request $request
     * @return Response
     */
    public function showSocialSupport(GroupPeople $groupPeople, SocialSupportGrp $socialSupportGrp, SocialSupportPers $socialSupportPers = null, Request $request): Response
    {
        $form = $this->createForm(SocialSupportGrpType::class, $socialSupportGrp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $socialSupportGrp
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $this->manager->persist($socialSupportGrp);

            // Met à jour le suivi social individuel pour chaque personne du groupe
            foreach ($socialSupportGrp->getSocialSupportPers() as $socialSupportPers) {

                if ($socialSupportPers->getEndDate() == null) {
                    $socialSupportPers->setEndDate($socialSupportGrp->getEndDate());
                }
                if ($socialSupportPers->getStatus() == 2) {
                    $socialSupportPers->setStatus($socialSupportGrp->getStatus());
                }
                $socialSupportPers->setUpdatedAt(new \DateTime());

                $this->manager->persist($socialSupportPers);
            };

            $this->manager->flush();

            $this->addFlash(
                "success",
                "Le suivi social a été modifié."
            );
        }
        return $this->render("app/socialSupport.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }
}
