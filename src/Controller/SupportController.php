<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\EvaluationGroup;
use App\Entity\PeopleGroup;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Entity\User;
use App\EntityManager\SupportManager;
use App\Form\Model\SupportSearch;
use App\Form\Model\SupportsInMonthSearch;
use App\Form\Support\NewSupportGroupType;
use App\Form\Support\SupportCoefficientType;
use App\Form\Support\SupportGroupType;
use App\Form\Support\SupportSearchType;
use App\Form\Support\SupportsInMonthSearchType;
use App\Form\Utils\Choices;
use App\Repository\ContributionRepository;
use App\Repository\DocumentRepository;
use App\Repository\EvaluationGroupRepository;
use App\Repository\NoteRepository;
use App\Repository\RdvRepository;
use App\Repository\ReferentRepository;
use App\Repository\ServiceRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use App\Service\Calendar;
use App\Service\Grammar;
use App\Service\Pagination;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Liste des suivis sociaux.
     *
     * @Route("/supports", name="supports", methods="GET|POST")
     */
    public function viewListSupports(Request $request, SupportManager $supportManager, SupportPersonRepository $repo, Pagination $pagination): Response
    {
        $search = (new SupportSearch())->setStatus([SupportGroup::STATUS_IN_PROGRESS]);

        $form = ($this->createForm(SupportSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $supportManager->exportData($search, $repo);
        }

        return $this->render('app/support/listSupports.html.twig', [
            'SupportSearch' => $search,
            'form' => $form->createView(),
            'supports' => $pagination->paginate($repo->findSupportsQuery($search), $request),
        ]);
    }

    /**
     * Nouveau suivi social.
     *
     * @Route("/group/{id}/support/new", name="support_new", methods="GET|POST")
     */
    public function newSupportGroup(PeopleGroup $peopleGroup, Request $request, SupportManager $supportManager, ServiceRepository $repoService): Response
    {
        $supportGroup = $supportManager->getNewSupportGroup($peopleGroup, $request, $repoService);

        $form = ($this->createForm(SupportGroupType::class, $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $supportGroup->getAgreement()) {
            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if ($supportManager->create($this->manager, $peopleGroup, $supportGroup, $form->get('cloneSupport')->getViewData() != null)) {
                $this->addFlash('success', 'Le suivi social est créé.');

                if ($supportGroup->getStartDate() && Choices::YES == $supportGroup->getService()->getAccommodation()
                    && Choices::YES == $supportGroup->getDevice()->getAccommodation()) {
                    return $this->redirectToRoute('support_accommodation_new', ['id' => $supportGroup->getId()]);
                }

                return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
            }
            $this->addFlash('danger', 'Attention, un suivi social est déjà en cours pour ce groupe.');
        }

        return $this->render('app/support/supportGroupEdit.html.twig', [
            'people_group' => $peopleGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Donne le formulaire pour créer un nouveau suivi social au groupe (via AJAX).
     *
     * @Route("/group/{id}/new_support", name="people_group_new_support", methods="GET")
     */
    public function newSupportGroupAjax(PeopleGroup $peopleGroup, SupportGroupRepository $repoSupportGroup)
    {
        $supportGroup = (new SupportGroup())->setReferent($this->getUser());

        $nbSupports = $repoSupportGroup->countSupportOfPeopleGroup($peopleGroup);

        $form = $this->createForm(NewSupportGroupType::class, $supportGroup, [
            'action' => $this->generateUrl('support_new', ['id' => $peopleGroup->getId()]),
        ]);

        return $this->json([
            'code' => 200,
            'data' => [
                'form' => $this->render('app/support/formNewSupport.html.twig', [
                    'form' => $form->createView(),
                    'nbSupports' => $nbSupports,
                ]),
            ],
        ], 200);
    }

    /**
     * Donne le formulaire pour éditer suivi social au groupe (via AJAX).
     *
     * @Route("/support/change_service", name="support_change_service", methods="GET|POST")
     */
    public function getSupportGroupType(Request $request)
    {
        $form = $this->createForm(NewSupportGroupType::class, new SupportGroup())
            ->handleRequest($request);

        return $this->render('app/support/formSupportGroup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un suivi social.
     *
     * @Route("/support/{id}/edit", name="support_edit", methods="GET|POST")
     */
    public function editSupportGroup(int $id, Request $request, SupportGroupRepository $repoSupportGroup, SupportManager $supportManager): Response
    {
        $supportGroup = $repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm(SupportGroupType::class, $supportGroup))
        ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supportManager->update($this->manager, $supportGroup);

            return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
        }

        $formCoeff = ($this->createForm(SupportCoefficientType::class, $supportGroup))
            ->handleRequest($request);

        if ($this->isGranted('ROLE_ADMIN') && $formCoeff->isSubmitted() && $formCoeff->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Le coefficient du suivi est mis à jour.');

            return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
        }

        return $this->render('app/support/supportGroupEdit.html.twig', [
            'form' => $form->createView(),
            'formCoeff' => $formCoeff->createView(),
        ]);
    }

    /**
     * Voir un suivi social.
     *
     * @Route("/support/{id}/view", name="support_view", methods="GET")
     */
    public function viewSupportGroup(
        int $id,
        SupportManager $supportManager,
        ReferentRepository $repoReferent,
        RdvRepository $repoRdv,
        NoteRepository $repoNote,
        DocumentRepository $repoDocument,
        ContributionRepository $repoContribution,
        EvaluationGroupRepository $repoEvaluation): Response
    {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        return $this->render('app/support/supportGroupView.html.twig', [
            'support' => $supportGroup,
            'referents' => $supportManager->getReferents($supportGroup->getPeopleGroup(), $repoReferent),
            'nbNotes' => $supportManager->getNbNotes($supportGroup, $repoNote),
            'nbRdvs' => $nbRdvs = $supportManager->getNbRdvs($supportGroup, $repoRdv),
            'nbDocuments' => $supportManager->getNbDocuments($supportGroup, $repoDocument),
            'nbContributions' => $supportManager->getNbContributions($supportGroup, $repoContribution),
            'lastRdv' => $nbRdvs ? $supportManager->getLastRdvs($supportGroup, $repoRdv) : null,
            'nextRdv' => $nbRdvs ? $supportManager->getNextRdvs($supportGroup, $repoRdv) : null,
            'evaluation' => $supportManager->getEvaluation($supportGroup, $repoEvaluation),
        ]);
    }

    /**
     * Supprime le suivi social du groupe.
     *
     * @Route("/support/{id}/delete", name="support_delete", methods="GET")
     * @IsGranted("DELETE", subject="supportGroup")
     */
    public function deleteSupportGroup(SupportGroup $supportGroup): Response
    {
        $this->manager->getFilters()->disable('softdeleteable');
        $this->manager->remove($supportGroup);
        $this->manager->flush();

        $this->addFlash('warning', 'Le suivi social est supprimé.');

        $peopleGroup = $supportGroup->getPeopleGroup();

        (new FilesystemAdapter())->deleteItem(PeopleGroup::CACHE_GROUP_SUPPORTS_KEY.$peopleGroup->getId());

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Ajout de personnes au suivi.
     *
     * @Route("/support/{id}/add_people", name="support_add_people", methods="GET")
     */
    public function addPeopleInSupport(SupportGroup $supportGroup, EvaluationGroupRepository $repo, SupportManager $supportManager): Response
    {
        if (!$supportManager->addPeopleInSupport($this->manager, $supportGroup, $repo)) {
            $this->addFlash('warning', "Aucune personne n'a été ajoutée au suivi.");
        }

        $this->discache($supportGroup);

        return $this->redirectToRoute('support_edit', [
            'id' => $supportGroup->getId(),
        ]);
    }

    /**
     * Retire la personne du suivi social.
     *
     * @Route("/supportGroup/{id}/remove-{support_pers_id}/{_token}", name="remove_support_pers", methods="GET")
     * @ParamConverter("supportPerson", options={"id" = "support_pers_id"})
     */
    public function removeSupportPerson(SupportGroup $supportGroup, string $_token, SupportPerson $supportPerson): Response
    {
        // Vérifie si le token est valide avant de retirer la personne du suivi social
        if ($this->isCsrfTokenValid('remove'.$supportPerson->getId(), $_token)) {
            // Vérifie si la personne est le demandeur principal
            if ($supportPerson->getHead()) {
                return $this->json([
                    'code' => 200,
                    'action' => null,
                    'alert' => 'danger',
                    'msg' => 'Le demandeur principal ne peut pas être retiré du suivi.',
                    'data' => null,
                ], 200);
            }

            try {
                $supportGroup->removeSupportPerson($supportPerson);
                $supportGroup->setNbPeople($supportGroup->getNbPeople() - 1);
                $this->manager->flush();

                $this->discache($supportGroup);

                return $this->json([
                'code' => 200,
                'action' => 'delete',
                'alert' => 'warning',
                'msg' => $supportPerson->getPerson()->getFullname().' est retiré'.Grammar::gender($supportPerson->getPerson()->getGender()).' du suivi social.',
                'data' => null,
            ], 200);
            } catch (\Throwable $th) {
            }
        }

        return $this->getErrorMessage();
    }

    /**
     * Affiche les suivis dans le mois.
     *
     * @Route("/supports/this_month", name="supports_current_month", methods="GET|POST")
     * @Route("/supports/{year}/{month}", name="supports_in_month", methods="GET|POST", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * })
     */
    public function showSupportsWithContribution(int $year = null, int $month = null, Request $request, SupportGroupRepository $repoSupportGroup, ContributionRepository $repoContribution, Pagination $pagination): Response
    {
        $search = new SupportsInMonthSearch();
        if (User::STATUS_SOCIAL_WORKER == $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = ($this->createForm(SupportsInMonthSearchType::class, $search))
            ->handleRequest($request);

        // if ($month == null) {
        //     $month = (new \DateTime())->modify('-1 month')->format('n');
        // }

        $calendar = new Calendar($year, $month);

        $supports = $pagination->paginate($repoSupportGroup->findSupportsBetween($calendar->getFirstDayOfTheMonth(), $calendar->getLastDayOfTheMonth(), $search), $request, 30);

        $supportsId = [];
        foreach ($supports->getItems() as $support) {
            $supportsId[] = $support->getId();
        }

        return $this->render('app/support/supportsInMonth.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
            'supports' => $supports,
            'contributions' => $repoContribution->findContributionsBetween($calendar->getFirstDayOfTheMonth(), $calendar->getLastDayOfTheMonth(), $supportsId),
        ]);
    }

    /**
     * Crée une copie d'un suivi social.
     *
     * @Route("/support/{id}/clone", name="support_clone", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function cloneSupport(SupportGroup $supportGroup, SupportManager $supportManager, SupportGroupRepository $repoSupportGroup): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        if ($supportManager->cloneSupport($supportGroup, $repoSupportGroup)) {
            $this->manager->flush();

            $this->addFlash('success', 'Les informations du précédent suivi ont été ajoutées (évaluation sociale, documents...)');
        } else {
            $this->addFlash('warning', 'Aucun autre suivi n\'a été trouvé.');
        }

        return $this->redirectToRoute('support_view', [
            'id' => $supportGroup->getId(),
        ]);
    }

    /**
     * Supprime le cache du suivi social et de l'évaluation sociale.
     */
    public function discache(SupportGroup $supportGroup): bool
    {
        $cache = new FilesystemAdapter();
        $id = $supportGroup->getId();

        if ($supportGroup->getReferent()) {
            $cache->deleteItem(User::CACHE_USER_SUPPORTS_KEY.$supportGroup->getReferent()->getId());
        }

        return $cache->deleteItems([
            SupportGroup::CACHE_SUPPORT_KEY.$id,
            SupportGroup::CACHE_FULLSUPPORT_KEY.$id,
            EvaluationGroup::CACHE_EVALUATION_KEY.$id,
        ]);
    }
}
