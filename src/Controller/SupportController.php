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
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GroupPeopleRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use App\Form\Support\SupportGroupSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Support\SupportGroupWithPeopleType;
use App\Service\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SupportController extends AbstractController
{
    private $manager;
    private $repoSupportGroup;
    private $repoSupportPerson;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, SupportPersonRepository $repoSupportPerson)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoSupportPerson = $repoSupportPerson;
    }

    /**
     * Liste des suivis sociaux
     * 
     * @Route("/supports", name="supports")
     * @param Request $request
     * @param SupportGroupSearch $supportGroupSearch
     * @param Pagination $pagination
     * @return Response
     */
    public function viewListSupports(Request $request, SupportGroupSearch $supportGroupSearch = null, Pagination $pagination): Response
    {
        $supportGroupSearch = new SupportGroupSearch();

        $form = $this->createForm(SupportGroupSearchType::class, $supportGroupSearch);
        $form->handleRequest($request);

        if ($supportGroupSearch->getExport()) {
            return $this->exportData($supportGroupSearch);
        }

        $supports = $pagination->paginate($this->repoSupportGroup->findAllSupportsQuery($supportGroupSearch), $request);

        return $this->render("app/listSupports.html.twig", [
            "supportGroupSearch" => $supportGroupSearch,
            "form" => $form->createView(),
            "supports" => $supports
        ]);
    }


    /**
     * Nouveau suivi social
     * 
     * @Route("/group/{id}/support/new", name="support_new", methods="GET|POST")
     * @param int $id
     * @param GroupPeopleRepository $repo
     * @param Request $request
     * @return Response
     */
    public function newSupportGroup($id, GroupPeopleRepository $repo, Request $request): Response
    {
        $groupPeople = $repo->findGroupPeopleById($id);

        $supportGroup = new SupportGroup();
        $supportGroup->setStartDate(new \DateTime());
        $supportGroup->setReferent($this->getUser());

        $form = $this->createForm(SupportGroupType::class, $supportGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if (!$this->activeSupport($groupPeople, $supportGroup)) {
                return $this->createSupportGroup($groupPeople, $supportGroup);
            }
            $this->addFlash("danger", "Attention, un suivi social est déjà en cours pour ce groupe.");
        }
        return $this->render("app/support/supportGroup.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un suvi social
     * 
     * @Route("/support/{id}", name="support_edit", methods="GET|POST")
     * @param SupportGroup $supportGroup
     * @param Request $request
     * @return Response
     */
    public function editSupportGroup(SupportGroup $supportGroup, Request $request): Response
    {
        $this->denyAccessUnlessGranted("VIEW", $supportGroup);

        $form = $this->createForm(SupportGroupType::class, $supportGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->tryEditSupportGroup($form, $supportGroup);
        }

        if (!$form->isSubmitted() && $supportGroup->getService()->getAccommodation() && count($supportGroup->getGroupPeopleAccommodations()) == 0) {
            $this->addFlash("warning", "Attention, aucun hébergement enregistré pour ce suivi.");
        }

        return $this->render("app/support/supportGroup.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Modification des suivis individuels
     * 
     * @Route("/support/{id}/people", name="support_pers_edit", methods="GET|POST")
     * @param SupportGroup $supportGroup
     * @param Request $request
     * @return Response
     */
    public function editSupportGroupleWithPeople(SupportGroup $supportGroup, Request $request): Response
    {
        $this->denyAccessUnlessGranted("VIEW", $supportGroup);

        $form = $this->createForm(SupportGroupWithPeopleType::class, $supportGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $supportGroup->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Le suivi social a été modifié.");
        }

        return $this->render("app/support/supportPerson.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Ajout de personnes au suivi
     * 
     * @Route("/support/{id}/add_people", name="support_add_people", methods="GET|POST")
     * @param SupportGroup $supportGroup
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

                $supportGroup->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($this->getUser());

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
     * 
     * @Route("/supportGroup/{id}/remove-{support_pers_id}_{_token}", name="remove_support_pers", methods="GET")
     * @ParamConverter("supportPerson", options={"id" = "support_pers_id"})
     * @param SupportGroup $supportGroup
     * @param SupportPerson $supportPerson
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
        return $this->errorMessage();
    }

    /**
     * Export des données
     * 
     * @Route("export", name="export")
     * @param Request $request
     * @param Export $export
     * @param SupportPersonFullExport $exportSupport
     * @return Response
     */
    public function export(Request $request, Export $export = null, SupportPersonFullExport $exportSupport): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $export = new Export();

        $form = $this->createForm(ExportType::class, $export);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supports = $this->repoSupportPerson->findSupportsFullToExport($export);
            return $exportSupport->exportData($supports);
        }

        return $this->render("app/export.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Exporte les données
     * @param SupportGroupSearch $supportGroupSearch
     */
    protected function exportData(SupportGroupSearch $supportGroupSearch)
    {
        $supports = $this->repoSupportPerson->findSupportsToExport($supportGroupSearch);

        $export = new SupportPersonExport();

        return $export->exportData($supports);
    }

    /**
     * Vérifie si un suivi social est déjà en cours dans le même service
     * @param GroupPeople $groupPeople
     * @param SupportGroup $supportGroup
     * @return SupportGroup|null
     */
    protected function activeSupport(GroupPeople $groupPeople, SupportGroup $supportGroup)
    {
        return $this->repoSupportGroup->findBy([
            "groupPeople" => $groupPeople,
            "status" => 2,
            "service" => $supportGroup->getService()
        ]);
    }

    /**
     * Crée un suivi
     * @param GroupPeople $groupPeople
     * @param SupportGroup $supportGroup
     * @return SupportGroup|null
     */
    protected function createSupportGroup(GroupPeople $groupPeople, SupportGroup $supportGroup)
    {
        $now = new \DateTime();

        $supportGroup->setGroupPeople($groupPeople)
            ->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($supportGroup);

        $this->createSupportPerson($groupPeople, $supportGroup);

        $this->manager->flush();

        $this->addFlash("success", "Le suivi social a été créé.");

        if ($supportGroup->getService()->getAccommodation()) {
            return $this->redirectToRoute("support_accommodation_new", [
                "id" => $supportGroup->getId()
            ]);
        }
        return $this->redirectToRoute("support_edit", [
            "id" => $supportGroup->getId()
        ]);
    }

    /**
     * Crée un suivi individuel
     * @param GroupPeople $groupPeople
     * @param SupportGroup $supportGroup
     */
    protected function createSupportPerson(GroupPeople $groupPeople, SupportGroup $supportGroup)
    {
        $now = new \DateTime();
        // Créé un suivi social individuel pour chaque personne du groupe
        foreach ($groupPeople->getRolePerson() as $rolePerson) {

            $supportPerson = new SupportPerson();

            $supportPerson->setSupportGroup($supportGroup)
                ->setPerson($rolePerson->getPerson())
                ->setStartDate($supportGroup->getStartDate())
                ->setEndDate($supportGroup->getEndDate())
                ->setStatus($supportGroup->getStatus())
                ->setCreatedAt($now)
                ->setUpdatedAt($now);

            $this->manager->persist($supportPerson);
        };
    }

    /**
     * Tente de mettre à jour le suivi
     * @param  $form
     * @param SupportGroup $supportGroup
     */
    protected function tryEditSupportGroup($form, SupportGroup $supportGroup)
    {
        if ($form->isValid()) {
            return $this->updateSupportGroup($supportGroup);
        }
        $errors = $form->getErrors(true);
        foreach ($errors as $error) {
            $errorOrigin = $error->getOrigin();
            $this->addFlash("danger", $errorOrigin->getName() . " : " . $error->getMessage());
        }
    }

    /**
     * Met à jour le suivi social
     * @param SupportGroup $supportGroup
     */
    protected function updateSupportGroup(SupportGroup $supportGroup)
    {
        $supportGroup
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->updateSupportPerson($supportGroup);

        $this->manager->flush();

        $this->addFlash("success", "Le suivi social a été modifié.");
    }

    /**
     * Met à jour les suivis individuelles
     * @param SupportGroup $supportGroup
     */
    protected function  updateSupportPerson(SupportGroup $supportGroup)
    {
        $ressourcesGroupAmt = 0;
        $chargesGroupAmt = 0;
        $debtsGroupAmt = 0;
        $monthlyRepaymentAmt = 0;

        foreach ($supportGroup->getSupportPerson() as $supportPerson) {

            if ($supportPerson->getEndDate() == null) {
                $supportPerson->setEndDate($supportGroup->getEndDate());
            }
            if ($supportPerson->getStatus() == 2) {
                $supportPerson->setStatus($supportGroup->getStatus());
            }
            $supportPerson->setUpdatedAt(new \DateTime());

            $sitBudget = $supportPerson->getSitBudget();
            $ressourcesGroupAmt += $sitBudget->getRessourcesAmt();
            $chargesGroupAmt += $sitBudget->getChargesAmt();
            $debtsGroupAmt += $sitBudget->getDebtsAmt();
            $monthlyRepaymentAmt += $sitBudget->getMonthlyRepaymentAmt();
        };

        $sitBudgetGroup = $supportGroup->getSitBudgetGroup();
        $sitBudgetGroup->setRessourcesGroupAmt($ressourcesGroupAmt);
        $sitBudgetGroup->setChargesGroupAmt($chargesGroupAmt);
        $sitBudgetGroup->setDebtsGroupAmt($debtsGroupAmt);
        $sitBudgetGroup->setMonthlyRepaymentAmt($monthlyRepaymentAmt);
        $sitBudgetGroup->setBudgetBalanceAmt($ressourcesGroupAmt - $chargesGroupAmt - $monthlyRepaymentAmt);
    }

    /**
     * Retourne un message d'erreur au format JSON
     * @return Response
     */
    protected function errorMessage(): Response
    {
        return $this->json([
            "code" => 403,
            "alert" => "danger",
            "msg" => "Une erreur s'est produite. La personne n'a pas été retiré du suivi.",
        ], 200);
    }
}
