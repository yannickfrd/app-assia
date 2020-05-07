<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Service\Grammar;
use App\Form\Model\Export;
use App\Service\Pagination;
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
use App\Repository\EvaluationGroupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SupportGroup\SupportGroupService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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
        $search = (new SupportGroupSearch())->setStatus([2]);

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
    public function newSupportGroup(int $id, GroupPeopleRepository $repo, Request $request, SupportGroupService $supportGroupService): Response
    {
        $groupPeople = $repo->findGroupPeopleById($id);

        $supportGroup = $supportGroupService->getNewSupportGroup($this->getUser());

        $form = ($this->createForm(SupportGroupType::class, $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si pas de suivi en cours, en crée un nouveau, sinon ne fait rien
            if ($supportGroupService->create($groupPeople, $supportGroup)) {
                $this->addFlash('success', 'Le suivi social est créé.');

                if ($supportGroup->getService()->getAccommodation()) {
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
     * Modification d'un suivi social.
     *
     * @Route("/support/{id}/edit", name="support_edit", methods="GET|POST")
     */
    public function editSupportGroup(int $id, Request $request, SupportGroupService $supportGroupService): Response
    {
        $supportGroup = $this->repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm(SupportGroupType::class, $supportGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supportGroupService->update($supportGroup);

            $this->manager->flush();

            $this->addFlash('success', 'Le suivi social est mis à jour.');

            return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
        }

        return $this->render('app/support/supportGroupEdit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Voir un suivi social.
     *
     * @Route("/support/{id}/view", name="support_view", methods="GET|POST")
     */
    public function viewSupportGroup(int $id, EvaluationGroupRepository $repo): Response
    {
        $cache = new FilesystemAdapter();

        // $cacheSupport = $cache->getItem('support_group'.$id);
        // if (!$cacheSupport->isHit()) {
        //     $cacheSupport->set($this->repoSupportGroup->findFullSupportById($id));
        //     // $cacheSupport->expiresAfter(365 * 24 * 60 * 60);  // 5 * 60 seconds
        //     $cache->save($cacheSupport);
        // }
        // $supportGroup = $cacheSupport->get();

        // $this->checkSupportGroup($supportGroup);

        $supportGroup = $this->repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $cacheEvaluation = $cache->getItem('support_group.evaluation'.$id);
        if (!$cacheEvaluation->isHit()) {
            $cacheEvaluation->set($repo->findEvaluationById($id));
            $cache->save($cacheEvaluation);
        }
        $evaluation = $cacheEvaluation->get();

        return $this->render('app/support/supportGroupView.html.twig', [
            'support' => $supportGroup,
            'evaluation' => $evaluation,
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
            $this->addFlash('warning', "Aucune personne n'est ajoutée au suivi.");
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
            $supportGroup->removeSupportPerson($supportPerson);
            $this->manager->flush();

            return $this->json([
                'code' => 200,
                'msg' => $supportPerson->getPerson()->getFullname().' est retiré'.Grammar::gender($supportPerson->getPerson()->getGender()).' du suivi social.',
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
     */
    public function export(Request $request, Export $export = null, SupportPersonFullExport $exportSupport): Response
    {
        $export = new Export();

        $form = ($this->createForm(ExportType::class, $export))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $supports = $this->repoSupportPerson->findSupportsFullToExport($export);
            // return $exportSupport->exportData($supports);
            // $this->addFlash('success', 'Votre export est en cours de préparation... Vous recevrez le lien de téléchargement par email.');

            return $this->json([
                'code' => 200,
                'alert' => 'success',
                'msg' => 'Votre fichier d\'export est prêt. Un mail vous a été envoyé.',
            ], 200);
        }

        return $this->render('app/export/export.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Vérifie la cohérence des données du suivi social.
     *
     * @param SupportGroup $supportGroup
     *
     * @return void
     */
    protected function checkSupportGroup(SupportGroup $supportGroup)
    {
        // Vérifie que le nombre de personnes suivies correspond à la composition familiale du groupe
        $nbSupportPeople = $supportGroup->getSupportPeople()->count();
        $nbPeople = $supportGroup->getGroupPeople()->getNbPeople();
        if ($nbSupportPeople != $nbPeople) {
            $this->addFlash('warning', 'Attention, le nombre de personnes rattachées au suivi ('.$nbSupportPeople.') 
                ne correspond pas à la composition familiale du groupe ('.$nbPeople.' personnes). 
                Cliquez sur le bouton [Modifier] pour ajouter les personnes au suivi.');
        }

        // Vérifie qu'il y a un hébergement créé
        if ($supportGroup->getService()->getAccommodation() && 0 == $supportGroup->getAccommodationGroups()->count()) {
            $this->addFlash('warning', 'Attention, aucun hébergement n\'est enregistré pour ce suivi.');
        } else {
            // Vérifie que le nombre de personnes suivies correspond au nombre de personnes hébergées
            $nbAccommodationPeople = 0;
            foreach ($supportGroup->getAccommodationGroups() as $accommodationGroup) {
                if (null == $accommodationGroup->getEndDate()) {
                    $nbAccommodationPeople += $accommodationGroup->getAccommodationPeople()->count();
                }
            }
            if ($supportGroup->getService()->getAccommodation() && $nbSupportPeople != $nbAccommodationPeople) {
                $this->addFlash('warning', 'Attention, le nombre de personnes rattachées au suivi ('.$nbSupportPeople.') 
                    ne correspond pas au nombre de personnes hébergées ('.$nbAccommodationPeople.') .
                    Allez dans l\'onglet [Hébergement] ci-dessous pour ajouter les personnes à l\'hébergement.');
            }
        }
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
}
