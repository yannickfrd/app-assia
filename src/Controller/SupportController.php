<?php

namespace App\Controller;

use App\Entity\EvaluationPerson;
use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Export\SupportPersonExport;
use App\Export\SupportPersonFullExport;
use App\Form\Export\ExportType;
use App\Form\Model\Export;
use App\Form\Model\SupportGroupSearch;
use App\Form\Support\SupportGroupSearchType;
use App\Form\Support\SupportGroupType;
use App\Form\Support\SupportGroupWithPeopleType;
use App\Repository\EvaluationGroupRepository;
use App\Repository\GroupPeopleRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    use ErrorMessageTrait;

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
     * Liste des suivis sociaux.
     *
     * @Route("/supports", name="supports", methods="GET|POST")
     *
     * @param SupportGroupSearch $supportGroupSearch
     */
    public function viewListSupports(Request $request, SupportGroupSearch $supportGroupSearch = null, Pagination $pagination): Response
    {
        $supportGroupSearch = new SupportGroupSearch();

        $form = ($this->createForm(SupportGroupSearchType::class, $supportGroupSearch))
            ->handleRequest($request);

        if ($supportGroupSearch->getExport()) {
            return $this->exportData($supportGroupSearch);
        }

        return $this->render('app/support/listSupports.html.twig', [
            'supportGroupSearch' => $supportGroupSearch,
            'form' => $form->createView(),
            'supports' => $pagination->paginate($this->repoSupportGroup->findAllSupportsQuery($supportGroupSearch), $request),
        ]);
    }

    /**
     * Nouveau suivi social.
     *
     * @Route("/group/{id}/support/new", name="support_new", methods="GET|POST")
     */
    public function newSupportGroup(int $id, GroupPeopleRepository $repo, Request $request): Response
    {
        $groupPeople = $repo->findGroupPeopleById($id);

        $supportGroup = (new SupportGroup())
            ->setStartDate(new \DateTime())
            ->setReferent($this->getUser());

        $form = ($this->createForm(SupportGroupType::class, $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if (!$this->activeSupport($groupPeople, $supportGroup)) {
                return $this->createSupportGroup($groupPeople, $supportGroup);
            }
            $this->addFlash('danger', 'Attention, un suivi social est déjà en cours pour ce groupe.');
        }

        return $this->render('app/support/supportGroup.html.twig', [
            'group_people' => $groupPeople,
            'form' => $form->createView(),
            'edit_mode' => false,
        ]);
    }

    /**
     * Modification d'un suivi social.
     *
     * @Route("/support/{id}", name="support_edit", methods="GET|POST")
     */
    public function editSupportGroup(int $id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm(SupportGroupType::class, $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateSupportGroup($supportGroup);
        }

        if (!$form->isSubmitted() && $supportGroup->getService()->getAccommodation() && 0 == count($supportGroup->getAccommodationGroups())) {
            $this->addFlash('warning', 'Attention, aucun hébergement enregistré pour ce suivi.');
        }

        return $this->render('app/support/supportGroup.html.twig', [
            'form' => $form->createView(),
            'edit_mode' => true,
        ]);
    }

    /**
     * Supprime le suivi social du groupe.
     *
     * @Route("/support/{id}/delete", name="support_delete", methods="GET")
     * @IsGranted("DELETE", subject="supportGroup")
     */
    public function deleteSupport(SupportGroup $supportGroup): Response
    {
        $this->manager->remove($supportGroup);
        $this->manager->flush();

        $this->addFlash('warning', 'Le suivi social a été supprimé.');

        return $this->redirectToRoute('group_people_show', ['id' => $supportGroup->getGroupPeople()->getId()]);
    }

    /**
     * Modification des suivis individuels.
     *
     * @Route("/support/{id}/people", name="support_pers_edit", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function editSupportGroupleWithPeople(int $id, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm(SupportGroupWithPeopleType::class, $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supportGroup->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());

            $this->manager->flush();

            $this->addFlash('success', 'Le suivi social a été modifié.');
        }

        return $this->render('app/support/supportPeople.html.twig', [
            'form' => $form->createView(),
            'edit_mode' => true,
        ]);
    }

    /**
     * Ajout de personnes au suivi.
     *
     * @Route("/support/{id}/add_people", name="support_add_people", methods="GET")
     */
    public function addPeopleInSupport(SupportGroup $supportGroup, EvaluationGroupRepository $repo): Response
    {
        $addPeople = false;

        foreach ($supportGroup->getGroupPeople()->getrolePerson() as $rolePerson) {
            if (!$this->personIsInSupport($rolePerson->getPerson(), $supportGroup)) {
                $supportPerson = $this->createSupportPerson($rolePerson, $supportGroup);

                $evaluationGroup = $repo->findLastEvaluationFromSupport($supportGroup);

                if ($evaluationGroup) {
                    $evaluationPerson = new EvaluationPerson();

                    $evaluationPerson->setEvaluationGroup($evaluationGroup)
                        ->setSupportPerson($supportPerson);

                    $this->manager->persist($evaluationPerson);
                }

                $addPeople = true;

                $this->addFlash('success', $rolePerson->getPerson()->getFullname().' a été ajouté(e) au suivi.');
            }
        }

        if ($addPeople) {
            $supportGroup->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());

            $this->manager->flush();
        } else {
            $this->addFlash('warning', "Aucune personne n'a été ajoutée au suivi.");
        }

        return $this->redirectToRoute('support_pers_edit', [
            'id' => $supportGroup->getId(),
        ]);
    }

    /**
     * Vérifie si la personne est déjà dans le suivi social.
     *
     * @return true|false
     */
    protected function personIsInSupport(Person $person, SupportGroup $supportGroup)
    {
        foreach ($supportGroup->getSupportPerson() as $supportPerson) {
            if ($person == $supportPerson->getPerson()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retire la personne du suivi social.
     *
     * @Route("/supportGroup/{id}/remove-{support_pers_id}_{_token}", name="remove_support_pers", methods="GET")
     * @ParamConverter("supportPerson", options={"id" = "support_pers_id"})
     */
    public function removeSupportPerson(SupportGroup $supportGroup, SupportPerson $supportPerson, Request $request): Response
    {
        // Vérifie si le token est valide avant de retirer la personne du suivi social
        if ($this->isCsrfTokenValid('remove'.$supportPerson->getId(), $request->get('_token'))) {
            $supportGroup->removeSupportPerson($supportPerson);
            $this->manager->flush();

            return $this->json([
                'code' => 200,
                'msg' => $supportPerson->getPerson()->getFullname().' a été retiré(e) du suivi social.',
                'data' => null,
            ], 200);
        }

        return $this->getErrorMessage();
    }

    /**
     * Export des données.
     *
     * @Route("export", name="export", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     *
     * @param Export $export
     */
    public function export(Request $request, Export $export = null, SupportPersonFullExport $exportSupport): Response
    {
        $export = new Export();

        $form = ($this->createForm(ExportType::class, $export))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supports = $this->repoSupportPerson->findSupportsFullToExport($export);

            return $exportSupport->exportData($supports);
        }

        return $this->render('app/export/export.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(SupportGroupSearch $supportGroupSearch)
    {
        $supports = $this->repoSupportPerson->findSupportsToExport($supportGroupSearch);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        $export = new SupportPersonExport();

        return $export->exportData($supports);
    }

    /**
     * Vérifie si un suivi social est déjà en cours dans le même service.
     *
     * @return SupportGroup|null
     */
    protected function activeSupport(GroupPeople $groupPeople, SupportGroup $supportGroup)
    {
        return $this->repoSupportGroup->findBy([
            'groupPeople' => $groupPeople,
            'status' => 2,
            'service' => $supportGroup->getService(),
        ]);
    }

    /**
     * Crée un suivi.
     */
    protected function createSupportGroup(GroupPeople $groupPeople, SupportGroup $supportGroup): Response
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
        }

        $this->manager->flush();

        $this->addFlash('success', 'Le suivi social a été créé.');

        if ($supportGroup->getService()->getAccommodation()) {
            return $this->redirectToRoute('support_accommodation_new', [
                'id' => $supportGroup->getId(),
            ]);
        }

        return $this->redirectToRoute('support_edit', [
            'id' => $supportGroup->getId(),
        ]);
    }

    /**
     * Crée un suivi individuel.
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
     * Met à jour le suivi social du groupe.
     */
    protected function updateSupportGroup(SupportGroup $supportGroup)
    {
        $supportGroup->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->updateSupportPeople($supportGroup);

        $this->manager->flush();

        $this->addFlash('success', 'Le suivi social a été modifié.');

        return;
    }

    /**
     * Met à jour le suivi social de la personne.
     */
    protected function updateSupportPeople(SupportGroup $supportGroup)
    {
        $nbPeople = count($supportGroup->getSupportPerson());
        foreach ($supportGroup->getSupportPerson() as $supportPerson) {
            if (1 == $nbPeople) {
                $supportPerson->setStartDate($supportGroup->getStartDate());
            }
            if (1 == $nbPeople || !$supportPerson->getEndDate()) {
                $supportPerson->setStatus($supportGroup->getStatus());
                $supportPerson->setEndDate($supportGroup->getEndDate());
                $supportPerson->setEndStatus($supportGroup->getEndStatus());
                $supportPerson->setEndStatusComment($supportGroup->getEndStatusComment());
            }
        }
    }
}
