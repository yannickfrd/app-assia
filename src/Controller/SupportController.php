<?php

namespace App\Controller;

use App\Utils\Agree;

use App\Entity\Person;

use App\Entity\RolePerson;
use App\Entity\GroupPeople;

use App\Form\GroupPeopleType;

use App\Entity\SupportGrp;
use App\Entity\GroupPeopleSearch;

use App\Entity\SupportPers;
use App\Form\SupportGrpType;
use App\Form\GroupPeopleSearchType;

use App\Repository\RolePersonRepository;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\SupportGrpRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\MakerBundle\Validator;

class SupportController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(ObjectManager $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    /**
     * @Route("/list/supports", name="list_supports")
     * @param RolePersonRepository $repo
     * @param GroupPeopleSearch $groupPeopleSearch
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function listSupports(RolePersonRepository $repo, GroupPeopleSearch $groupPeopleSearch = null, Request $request, PaginatorInterface $paginator): Response
    {
        $groupPeopleSearch = new GroupPeopleSearch();

        $form = $this->createForm(GroupPeopleSearchType::class, $groupPeopleSearch);
        $form->handleRequest($request);

        $rolePeople =  $paginator->paginate(
            $repo->findAllSupports($groupPeopleSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $rolePeople->setPageRange(5);
        $rolePeople->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/listSupports.html.twig", [
            "controller_name" => "listSupports",
            "role_people" => $rolePeople,
            "form" => $form->createView(),
            "current_menu" => "supports"
        ]);
    }

    /**
     * Crée un nouveau suivi social
     * 
     * @Route("/group/{id}/support/new", name="support_new", methods="GET|POST")
     * @param GroupPeople $groupPeople
     * @param SupportGrpRepository $repo
     * @param Request $request
     * @return Response
     */
    public function newSupport(GroupPeople $groupPeople, SupportGrpRepository $repo, Request $request): Response
    {
        $supportGrp = new SupportGrp();

        $form = $this->createForm(SupportGrpType::class, $supportGrp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si un suivi social est déjà en cours
            $activeSupport = $repo->findBy([
                "groupPeople" => $groupPeople,
                "status" => 2,
                "service" => $supportGrp->getService()
            ]);

            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if (!$activeSupport) {

                $user = $this->security->getUser();

                $supportGrp->setGroupPeople($groupPeople)
                    // ->setService($service)
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($user)
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($user);

                $this->manager->persist($supportGrp);

                // Créé un suivi social individuel pour chaque personne du groupe
                foreach ($groupPeople->getRolePerson() as $rolePerson) {
                    $supportPers = new SupportPers();

                    $supportPers->setSupportGrp($supportGrp)
                        ->setPerson($rolePerson->getPerson())
                        ->setStartDate($supportGrp->getStartDate())
                        ->setEndDate($supportGrp->getEndDate())
                        ->setStatus($supportGrp->getStatus())
                        ->setCreatedAt(new \DateTime())
                        ->setUpdatedAt(new \DateTime());
                    $this->manager->persist($supportPers);
                };

                $this->manager->flush();

                $this->addFlash(
                    "success",
                    "Le suivi social a été créé."
                );
                return $this->redirectToRoute("support_show", [
                    "id" => $groupPeople->getId(),
                    "support_id" => $supportGrp->getId()
                ]);
            } else {
                $this->addFlash(
                    "danger",
                    "Attention, un suivi social est déjà en cours pour ce groupe."
                );
            }
        }
        return $this->render("app/support.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Voir un suvi social
     * 
     * @Route("/group/{id}/support/{support_id}", name="support_show", methods="GET|POST")
     * @ParamConverter("supportGrp", options={"id" = "support_id"})
     * @param GroupPeople $groupPeople
     * @param SupportGrp $supportGrp
     * @param SupportPers $supportPers
     * @param SupportGrpRepository $repo
     * @param Request $request
     * @return Response
     */
    public function showSupport(GroupPeople $groupPeople, SupportGrp $supportGrp, SupportPers $supportPers = null, Request $request): Response
    {
        $form = $this->createForm(SupportGrpType::class, $supportGrp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $supportGrp
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $ressourcesGrpAmt = 0;
            $chargesGrpAmt = 0;
            $debtsGrpAmt = 0;
            $monthlyRepaymentAmt = 0;
            // Met à jour le suivi social individuel pour chaque personne du groupe
            foreach ($supportGrp->getSupportPers() as $supportPers) {

                if ($supportPers->getEndDate() == null) {
                    $supportPers->setEndDate($supportGrp->getEndDate());
                }
                if ($supportPers->getStatus() == 2) {
                    $supportPers->setStatus($supportGrp->getStatus());
                }
                $supportPers->setUpdatedAt(new \DateTime());

                $ressourcesGrpAmt += $supportPers->getSitBudget()->getRessourcesAmt();
                $chargesGrpAmt += $supportPers->getSitBudget()->getChargesAmt();
                $debtsGrpAmt += $supportPers->getSitBudget()->getDebtsAmt();
                $monthlyRepaymentAmt += $supportPers->getSitBudget()->getMonthlyRepaymentAmt();

                $this->manager->persist($supportPers);
            };

            $budgetBalanceAmt = $ressourcesGrpAmt - $chargesGrpAmt - $monthlyRepaymentAmt;

            $supportGrp->getSitBudgetGrp()->setRessourcesGrpAmt($ressourcesGrpAmt);
            $supportGrp->getSitBudgetGrp()->setChargesGrpAmt($chargesGrpAmt);
            $supportGrp->getSitBudgetGrp()->setDebtsGrpAmt($debtsGrpAmt);
            $supportGrp->getSitBudgetGrp()->setMonthlyRepaymentAmt($monthlyRepaymentAmt);
            $supportGrp->getSitBudgetGrp()->setBudgetBalanceAmt($budgetBalanceAmt);

            $this->manager->persist($supportGrp);

            $this->manager->flush();

            $this->addFlash(
                "success",
                "Le suivi social a été modifié."
            );
        }

        // Si erreur de validation
        if ($form->isSubmitted() && !$form->isValid()) {

            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $errorOrigin = $error->getOrigin();
                $this->addFlash(
                    "danger",
                    $errorOrigin->getName() . " : " . $error->getMessage()
                );
            }
        }

        return $this->render("app/support.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }
}
