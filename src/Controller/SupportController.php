<?php

namespace App\Controller;

use App\Entity\EvaluationPerson;
use App\Form\Model\Export;
use App\Entity\GroupPeople;
use App\Entity\RolePerson;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Form\Export\ExportType;
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
use App\Repository\EvaluationGroupRepository;
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

        return $this->render("app/support/listSupports.html.twig", [
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
    public function newSupportGroup(int $id, GroupPeopleRepository $repo, Request $request): Response
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
     * @param int $id
     * @param Request $request
     * @return Response
     */
    public function editSupportGroup(int $id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $form = $this->createForm(SupportGroupType::class, $supportGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateSupportGroup($supportGroup);
        }

        if (!$form->isSubmitted() && $supportGroup->getService()->getAccommodation() && count($supportGroup->getAccommodationGroups()) == 0) {
            $this->addFlash("warning", "Attention, aucun hébergement enregistré pour ce suivi.");
        }

        return $this->render("app/support/supportGroup.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Supprime le suivi social du groupe
     * 
     * @Route("/support/{id}/delete", name="support_delete")
     * @param SupportGroup $supportGroup
     * @return Response
     */
    public function deleteSupport(SupportGroup $supportGroup): Response
    {
        $this->denyAccessUnlessGranted("DELETE", $supportGroup);

        $this->manager->remove($supportGroup);
        $this->manager->flush();

        $this->addFlash("danger", "Le suivi social a été supprimé.");

        return $this->redirectToRoute("group_people_show", ["id" => $supportGroup->getGroupPeople()->getId()]);
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
        // $supportGroup = $this->repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $form = $this->createForm(SupportGroupWithPeopleType::class, $supportGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $supportGroup->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Le suivi social a été modifié.");
        }

        return $this->render("app/support/supportPeople.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Ajout de personnes au suivi
     * 
     * @Route("/support/{id}/add_people", name="support_add_people", methods="GET|POST")
     * @param SupportGroup $supportGroup
     */
    public function addPeopleInSupport(SupportGroup $supportGroup, EvaluationGroupRepository $repo): Response
    {
        $supportGroup->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $people = [];

        foreach ($supportGroup->getSupportPerson() as $supportPerson) {
            $people[] = $supportPerson->getPerson()->getId();
        }

        foreach ($supportGroup->getGroupPeople()->getrolePerson() as $rolePerson) {

            $personId = $rolePerson->getPerson()->getId();

            if (!in_array($personId, $people)) {

                $evaluationPerson = new EvaluationPerson();

                $evaluationGroup =  $repo->findLastEvaluationFromSupport($supportGroup);

                if ($evaluationGroup) {
                    $evaluationPerson->setEvaluationGroup($evaluationGroup)
                        ->setSupportPerson($this->createSupportPerson($rolePerson, $supportGroup));

                    $this->manager->persist($evaluationPerson);
                }
            }
        }
        $this->manager->flush();

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

        return $this->render("app/export/export.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Exporte les données
     * 
     * @param SupportGroupSearch $supportGroupSearch
     */
    protected function exportData(SupportGroupSearch $supportGroupSearch)
    {
        $supports = $this->repoSupportPerson->findSupportsToExport($supportGroupSearch);

        if (!$supports) {

            $this->addFlash("warning", "Aucun résultat à exporter.");

            return $this->redirectToRoute("supports");
        }

        $export = new SupportPersonExport();

        return $export->exportData($supports);
    }

    /**
     * Vérifie si un suivi social est déjà en cours dans le même service
     * 
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
     * 
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

        // Créé un suivi social individuel pour chaque personne du groupe
        foreach ($groupPeople->getRolePerson() as $rolePerson) {
            $this->createSupportPerson($rolePerson, $supportGroup);
        };

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
     * 
     * @param RolePerson $rolePerson
     * @param SupportGroup $supportGroup
     */
    protected function createSupportPerson(RolePerson $rolePerson, SupportGroup $supportGroup)
    {
        $now = new \DateTime();
        $supportPerson = new SupportPerson();

        $supportPerson->setSupportGroup($supportGroup)
            ->setPerson($rolePerson->getPerson())
            ->setHead($rolePerson->getHead())
            ->setRole($rolePerson->getRole())
            ->setStartDate($supportGroup->getStartDate())
            ->setEndDate($supportGroup->getEndDate())
            ->setStatus($supportGroup->getStatus())
            ->setCreatedAt($now)
            ->setUpdatedAt($now);

        $this->manager->persist($supportPerson);

        return $supportPerson;
    }

    /**
     * Met à jour le suivi social
     * 
     * @param SupportGroup $supportGroup
     */
    protected function updateSupportGroup(SupportGroup $supportGroup)
    {
        $supportGroup->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        if ($supportGroup->getEndStatus()) {
            foreach ($supportGroup->getSupportPerson() as $supportPerson) {
                $supportPerson->setendStatus($supportGroup->getEndStatus());
                $supportPerson->setendStatusComment($supportGroup->getEndStatusComment());
                $this->manager->persist($supportPerson);
            }
        }

        $this->manager->flush();

        return $this->addFlash("success", "Le suivi social a été modifié.");
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
