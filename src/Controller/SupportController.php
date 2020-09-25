<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\GroupPeople;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Entity\User;
use App\Export\SupportPersonExport;
use App\Form\Model\SupportGroupSearch;
use App\Form\Model\SupportsInMonthSearch;
use App\Form\Support\NewSupportGroupType;
use App\Form\Support\SupportCoefficientType;
use App\Form\Support\SupportGroupAvdlType;
use App\Form\Support\SupportGroupHotelType;
use App\Form\Support\SupportGroupSearchType;
use App\Form\Support\SupportGroupType;
use App\Form\Support\SupportsInMonthSearchType;
use App\Form\Utils\Choices;
use App\Repository\ContributionRepository;
use App\Repository\DocumentRepository;
use App\Repository\EvaluationGroupRepository;
use App\Repository\NoteRepository;
use App\Repository\RdvRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use App\Service\Calendar;
use App\Service\Grammar;
use App\Service\Indicators\SocialIndicators;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportGroupService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
     */
    public function viewListSupports(Request $request, SupportGroupSearch $search = null, Pagination $pagination): Response
    {
        $search = (new SupportGroupSearch())->setStatus([SupportGroup::STATUS_IN_PROGRESS]);

        $form = ($this->createForm(SupportGroupSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/support/listSupports.html.twig', [
            'supportGroupSearch' => $search,
            'form' => $form->createView(),
            'supports' => $pagination->paginate($this->repoSupportGroup->findAllSupportsQuery($search), $request),
        ]);
    }

    /**
     * Nouveau suivi social.
     *
     * @Route("/group/{id}/support/new", name="support_new", methods="GET|POST")
     */
    public function newSupportGroup(GroupPeople $groupPeople, Request $request, SupportGroupService $supportGroupService): Response
    {
        $serviceId = $request->request->get('support')['service'] ?? $_POST['support']['service'];

        $supportGroup = $supportGroupService->getNewSupportGroup($this->getUser(), $groupPeople, $serviceId);

        $form = ($this->createForm($this->getFormType($serviceId), $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $supportGroup->getAgreement()) {
            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if ($supportGroupService->create($groupPeople, $supportGroup)) {
                $this->addFlash('success', 'Le suivi social est créé.');

                if ($supportGroup->getStartDate() && $supportGroup->getService()->getAccommodation() == Choices::YES) {
                    return $this->redirectToRoute('support_accommodation_new', ['id' => $supportGroup->getId()]);
                }

                return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
            }
            $this->addFlash('danger', 'Attention, un suivi social est déjà en cours pour ce groupe.');
        }

        return $this->render('app/support/supportGroupEdit.html.twig', [
            'group_people' => $groupPeople,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Donne le formulaire pour créer un nouveau suivi social au gorupe (via AJAX).
     *
     * @Route("/group/{id}/new_support", name="group_people_new_support", methods="GET")
     */
    public function newSupportGroupAjax(GroupPeople $groupPeople)
    {
        $supportGroup = (new SupportGroup())
            ->setStatus(2)
            ->setReferent($this->getUser());

        $forwNewSupport = $this->createForm(NewSupportGroupType::class, $supportGroup, [
            'action' => $this->generateUrl('support_new', ['id' => $groupPeople->getId()]),
        ]);

        return $this->json([
            'code' => 200,
            'data' => [
                'form' => $this->render('app/support/formNewSupport.html.twig', [
                    'form_new_support' => $forwNewSupport->createView(),
                ]),
            ],
        ], 200);
    }

    /**
     * Modification d'un suivi social.
     *
     * @Route("/support/{id}/edit", name="support_edit", methods="GET|POST")
     */
    public function editSupportGroup(int $id, Request $request, SupportGroupService $supportGroupService): Response
    {
        $supportGroup = $supportGroupService->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm($this->getFormType($supportGroup->getService()->getId()), $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supportGroupService->update($supportGroup);

            $this->manager->flush();

            $this->addFlash('success', 'Le suivi social est mis à jour.');

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
     * @Route("/support/{id}/view", name="support_view", methods="GET|POST")
     */
    public function viewSupportGroup(int $id, SupportGroupService $supportGroupService, RdvRepository $repoRdv, NoteRepository $repoNote, DocumentRepository $repoDocument, ContributionRepository $repoContribution): Response
    {
        $supportGroup = $supportGroupService->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $nbRdvs = $repoRdv->count(['supportGroup' => $supportGroup->getId()]);

        return $this->render('app/support/supportGroupView.html.twig', [
            'support' => $supportGroup,
            'nbRdvs' => $nbRdvs,
            'nbNotes' => $repoRdv->count(['supportGroup' => $supportGroup->getId()]),
            'nbDocuments' => $repoDocument->count(['supportGroup' => $supportGroup->getId()]),
            'nbContributions' => $supportGroup->getAccommodationGroups() ? $repoContribution->count(['supportGroup' => $supportGroup->getId()]) : null,
            'evaluation' => $supportGroupService->getEvaluation($supportGroup),
            'nextRdv' => $nbRdvs ? $repoRdv->findNextRdvFromSupport($supportGroup->getId()) : null,
            'lastRdv' => $nbRdvs ? $repoRdv->findLastRdvFromSupport($supportGroup->getId()) : null,
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

        return $this->redirectToRoute('group_people_show', ['id' => $supportGroup->getGroupPeople()->getId()]);
    }

    /**
     * Ajout de personnes au suivi.
     *
     * @Route("/support/{id}/add_people", name="support_add_people", methods="GET")
     */
    public function addPeopleInSupport(SupportGroup $supportGroup, EvaluationGroupRepository $repo, SupportGroupService $supportGroupService): Response
    {
        if (!$supportGroupService->addPeopleInSupport($supportGroup, $repo)) {
            $this->addFlash('warning', "Aucune personne n'a été ajoutée au suivi.");
        }

        return $this->redirectToRoute('support_edit', [
            'id' => $supportGroup->getId(),
        ]);
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
            // Vérifie si la personne est le demandeur principal
            if ($supportPerson->getHead()) {
                return $this->json([
                    'code' => 200,
                    'action' => 'nothing',
                    'alert' => 'danger',
                    'msg' => 'Le demandeur principal ne peut pas être retiré du suivi.',
                    'data' => null,
                ], 200);
            }

            $supportGroup->removeSupportPerson($supportPerson);

            $supportGroup->setNbPeople($supportGroup->getNbPeople() - 1);

            $this->manager->flush();

            return $this->json([
                'code' => 200,
                'action' => 'delete',
                'alert' => 'warning',
                'msg' => $supportPerson->getPerson()->getFullname().' est retiré'.Grammar::gender($supportPerson->getPerson()->getGender()).' du suivi social.',
                'data' => null,
            ], 200);
        }

        return $this->getErrorMessage();
    }

    /**
     * @Route("/indicators/social", name="indicators_social", methods="GET|POST")
     */
    public function showSocialIndicators(Request $request, SupportGroupSearch $search = null, SocialIndicators $socialIndicators): Response
    {
        $search = new SupportGroupSearch();

        $form = ($this->createForm(SupportGroupSearchType::class, $search))
            ->handleRequest($request);

        $supports = $this->repoSupportPerson->findSupportsFullToExport($search);

        $datas = $socialIndicators->getResults($supports);

        return $this->render('app/evaluation/socialIndicators.html.twig', [
            'supportGroupSearch' => $search,
            'form' => $form->createView(),
            'datas' => $datas,
        ]);
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
    public function showSupportsWithContribution(int $year = null, int $month = null, Request $request, SupportsInMonthSearch $search = null, ContributionRepository $repoContribution, Pagination $pagination): Response
    {
        $search = new SupportsInMonthSearch();
        if ($this->getUser()->getStatus() == User::STATUS_SOCIAL_WORKER) {
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

        $supports = $pagination->paginate($this->repoSupportGroup->findSupportsBetween($calendar->getFirstDayOfTheMonth(), $calendar->getLastDayOfTheMonth(), $search), $request, 30);

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
    public function cloneSupport(SupportGroup $supportGroup, SupportGroupService $supportGroupService, NormalizerInterface $normalizer): Response
    {
        // $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $newSupportGroup = $supportGroupService->cloneSupport($supportGroup, $this->getUser(), $normalizer);

        $this->manager->flush();

        $this->addFlash('success', 'Le suivi social a été dupliqué.');

        return $this->redirectToRoute('support_edit', [
            'id' => $newSupportGroup->getId(),
        ]);
    }

    /**
     * Change le service d'un suivi.
     *
     * @Route("/support/{id}/switch_service/{service_id}", name="support_switch_service", methods="GET")
     * @ParamConverter("service", options={"id" = "service_id"})
     */
    public function switchService(SupportGroup $supportGroup, Service $service)
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $supportGroup
            ->setService($service)
            ->setDevice(null);

        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'reload',
            'alert' => 'warning',
            'msg' => 'La page va se recharger...',
        ], 200);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(SupportGroupSearch $search)
    {
        $supports = $this->repoSupportPerson->findSupportsToExport($search);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new SupportPersonExport())->exportData($supports);
    }

    /**
     * Donne le formType en fonction du service choisi.
     */
    protected function getFormType(int $serviceId = null)
    {
        if ($serviceId == Service::SERVICE_AVDL_ID) {
            return SupportGroupAvdlType::class;
        }
        if ($serviceId == Service::SERVICE_PASH_ID) {
            return SupportGroupHotelType::class;
        }

        return SupportGroupType::class;
    }
}
