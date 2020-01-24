<?php

namespace App\Controller;

use App\Form\Model\Export;
use App\Entity\GroupPeople;
use App\Entity\SupportGroup;

use App\Entity\SupportPerson;
use App\Form\Support\ExportType;
use App\Export\SupportPersonExport;
use App\Form\Model\SupportGroupSearch;
use App\Form\Support\SupportGroupType;

use App\Export\SupportPersonFullExport;
use App\Form\Support\SupportGroupType2;

use App\Repository\RolePersonRepository;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\Support\SupportGroupSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SupportController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(EntityManagerInterface $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    /**
     * Voir la liste des suivis sociaux
     * 
     * @Route("/supports", name="supports")
     * @param RolePersonRepository $repo
     * @param SupportGroupSearch $supportGroupSearch
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function viewListSupports(SupportGroupRepository $repo, SupportPersonRepository $repoSupportPerson, SupportGroupSearch $supportGroupSearch = null, Request $request, PaginatorInterface $paginator): Response
    {
        $supportGroupSearch = new SupportGroupSearch();

        $form = $this->createForm(SupportGroupSearchType::class, $supportGroupSearch);
        $form->handleRequest($request);

        if ($supportGroupSearch->getExport()) {
            $supports = $repoSupportPerson->findSupportsToExport($supportGroupSearch);
            $export = new SupportPersonExport();
            return $export->exportData($supports);
        }

        $supports = $this->paginate($paginator,  $repo,  $supportGroupSearch,  $request);

        return $this->render("app/listSupports.html.twig", [
            "supports" => $supports,
            "form" => $form->createView()
        ]);
    }

    /**
     * Crée un nouveau suivi social
     * 
     * @Route("/group/{id}/support/new", name="support_new", methods="GET|POST")
     * @param GroupPeople $groupPeople
     * @param SupportGroupRepository $repo
     * @param Request $request
     * @return Response
     */
    public function createSupport(GroupPeople $groupPeople, SupportGroupRepository $repo, Request $request): Response
    {
        $supportGroup = new SupportGroup();

        $form = $this->createForm(SupportGroupType::class, $supportGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si un suivi social est déjà en cours
            $activeSupport = $repo->findBy([
                "groupPeople" => $groupPeople,
                "status" => 2,
                "service" => $supportGroup->getService()
            ]);

            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if (!$activeSupport) {

                $user = $this->security->getUser();

                $supportGroup->setGroupPeople($groupPeople)
                    ->setReferent($user)
                    // ->setService($service)
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($user)
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($user);

                $this->manager->persist($supportGroup);

                // Créé un suivi social individuel pour chaque personne du groupe
                foreach ($groupPeople->getRolePerson() as $rolePerson) {
                    $supportPerson = new SupportPerson();

                    $supportPerson->setSupportGroup($supportGroup)
                        ->setPerson($rolePerson->getPerson())
                        ->setStartDate($supportGroup->getStartDate())
                        ->setEndDate($supportGroup->getEndDate())
                        ->setStatus($supportGroup->getStatus())
                        ->setCreatedAt(new \DateTime())
                        ->setUpdatedAt(new \DateTime());
                    $this->manager->persist($supportPerson);
                };

                $this->manager->flush();

                $this->addFlash("success", "Le suivi social a été créé.");
                return $this->redirectToRoute("support_edit", [
                    "id" => $supportGroup->getId()
                ]);
            } else {
                $this->addFlash("danger", "Attention, un suivi social est déjà en cours pour ce groupe.");
            }
        }
        return $this->render("app/supportGroup.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Voir un suvi social
     * 
     * @Route("/support/{id}", name="support_edit", methods="GET|POST")
     * @param SupportGroup $supportGroup
     * @param SupportPerson $supportPerson
     * @param Request $request
     * @return Response
     */
    public function editSupport(SupportGroup $supportGroup, SupportPerson $supportPerson = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $form = $this->createForm(SupportGroupType::class, $supportGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $supportGroup
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $ressourcesGroupAmt = 0;
            $chargesGroupAmt = 0;
            $debtsGroupAmt = 0;
            $monthlyRepaymentAmt = 0;

            // Met à jour le suivi social individuel pour chaque personne du groupe
            foreach ($supportGroup->getSupportPerson() as $supportPerson) {

                if ($supportPerson->getEndDate() == null) {
                    $supportPerson->setEndDate($supportGroup->getEndDate());
                }
                if ($supportPerson->getStatus() == 2) {
                    $supportPerson->setStatus($supportGroup->getStatus());
                }
                $supportPerson->setUpdatedAt(new \DateTime());

                $ressourcesGroupAmt += $supportPerson->getSitBudget()->getRessourcesAmt();
                $chargesGroupAmt += $supportPerson->getSitBudget()->getChargesAmt();
                $debtsGroupAmt += $supportPerson->getSitBudget()->getDebtsAmt();
                $monthlyRepaymentAmt += $supportPerson->getSitBudget()->getMonthlyRepaymentAmt();

                $this->manager->persist($supportPerson);
            };

            $budgetBalanceAmt = $ressourcesGroupAmt - $chargesGroupAmt - $monthlyRepaymentAmt;

            $supportGroup->getSitBudgetGroup()->setRessourcesGroupAmt($ressourcesGroupAmt);
            $supportGroup->getSitBudgetGroup()->setChargesGroupAmt($chargesGroupAmt);
            $supportGroup->getSitBudgetGroup()->setDebtsGroupAmt($debtsGroupAmt);
            $supportGroup->getSitBudgetGroup()->setMonthlyRepaymentAmt($monthlyRepaymentAmt);
            $supportGroup->getSitBudgetGroup()->setBudgetBalanceAmt($budgetBalanceAmt);

            $this->manager->persist($supportGroup);

            $this->manager->flush();

            $this->addFlash("success", "Le suivi social a été modifié.");
        }

        // Si erreur de validation
        if ($form->isSubmitted() && !$form->isValid()) {

            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
                $errorOrigin = $error->getOrigin();
                $this->addFlash("danger", $errorOrigin->getName() . " : " . $error->getMessage());
            }
        }

        return $this->render("app/supportGroup.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Voir les dates individuelles du suivi social
     * 
     * @Route("/support/{id}/individuals", name="support_pers_edit", methods="GET|POST")
     * @param GroupPeople $groupPeople
     * @param SupportGroup $supportGroup
     * @param SupportPerson $supportPerson
     * @param Request $request
     * @return Response
     */
    public function editSupportPerson(SupportGroup $supportGroup, Request $request): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $form = $this->createForm(SupportGroupType2::class, $supportGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $supportGroup
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $this->manager->persist($supportGroup);

            $this->manager->flush();

            $this->addFlash("success", "Le suivi social a été modifié.");
        }

        return $this->render("app/support/supportPerson.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }


    /**
     * Ajout de personnes au suivi
     * 
     * @Route("/support/{id}/add_people", name="support_add_people", methods="GET|POST")
     * @param GroupPeople $groupPeople
     * @param SupportGroup $supportGroup
     * @param SupportPerson $supportPerson
     */
    public function addPeopleInSupport(SupportGroup $supportGroup): Response
    {
        $people = [];

        foreach ($supportGroup->getSupportPerson() as $supportPerson) {
            $people[] = $supportPerson->getPerson()->getId();
        }

        foreach ($supportGroup->getGroupPeople()->getrolePerson() as $role) {

            $personId = $role->getPerson()->getId();

            if (!in_array($personId, $people)) {

                $user = $this->security->getUser();

                $supportGroup->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($user);

                $this->manager->persist($supportGroup);

                // Crée un suivi social individuel
                $supportPerson = new SupportPerson();

                $supportPerson->setSupportGroup($supportGroup)
                    ->setPerson($role->getPerson())
                    ->setStartDate(new \DateTime())
                    ->setEndDate($supportGroup->getEndDate())
                    ->setStatus($supportGroup->getStatus())
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime());

                $this->manager->persist($supportPerson);
            }

            $this->manager->flush();
        }
        return $this->redirectToRoute("support_pers_edit", [
            "id" => $supportGroup->getId()
        ]);
    }

    /**
     * Retire la personne du suivi social
     * @Route("/supportGroup/{id}/remove-{support_pers_id}_{_token}", name="remove_support_pers", methods="GET")
     * @ParamConverter("supportPerson", options={"id" = "support_pers_id"})
     * @param Request $request
     * @return Response
     */
    public function removeSupportPerson(SupportGroup $supportGroup, SupportPerson $supportPerson, Request $request): Response
    {
        // Vérifie si le token est valide avant de retirer la personne du suivi social
        if ($this->isCsrfTokenValid("remove" . $supportPerson->getId(), $request->get("_token"))) {

            $supportGroup->removeSupportPerson($supportPerson);

            $this->manager->flush();

            return $this->json([
                "code" => 200,
                "msg" => "La personne a été retirée du suivi social.",
                "data" => null
            ], 200);
        }
        return $this->json([
            "code" => 403,
            "msg" => "Une erreur s'est produite.",
            "data" => null
        ], 200);
    }

    /**
     * Export des données
     * 
     * @Route("export", name="export")
     * @param Export $export
     * @param Request $request
     * @param SupportPersonRepository $repoSupportPerson
     * @return Response
     */
    public function export(Export $export = null, Request $request, SupportPersonRepository $repoSupportPerson, SupportPersonFullExport $exportSupport): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $export = new Export();

        $form = $this->createForm(ExportType::class, $export);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supports = $repoSupportPerson->findSupportsFullToExport($export);
            return $exportSupport->exportData($supports);
        }

        return $this->render("app/export.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Pagination de la liste des suivis sociaux
     *
     * @param PaginatorInterface $paginator
     * @param SupportGroupRepository $repo
     * @param DocumentSearch $documentSearch
     * @param Request $request
     */
    protected function paginate(PaginatorInterface $paginator, SupportGroupRepository $repo, SupportGroupSearch $supportGroupSearch, Request $request)
    {
        $supports =  $paginator->paginate(
            $repo->findAllSupportsQuery($supportGroupSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );

        $supports->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $supports;
    }
}
