<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportSearch;
use App\Form\Model\Support\SupportsInMonthSearch;
use App\Form\Support\Support\AddPersonToSupportType;
use App\Form\Support\Support\NewSupportGroupType;
use App\Form\Support\Support\SupportCoefficientType;
use App\Form\Support\Support\SupportGroupType;
use App\Form\Support\Support\SupportSearchType;
use App\Form\Support\Support\SupportsInMonthSearchType;
use App\Form\Support\Support\SwitchSupportReferentType;
use App\Repository\Support\PaymentRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Calendar;
use App\Service\Export\SupportPersonExport;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportChecker;
use App\Service\SupportGroup\SupportCollections;
use App\Service\SupportGroup\SupportDuplicator;
use App\Service\SupportGroup\SupportManager;
use App\Service\SupportGroup\SupportReferentSwitcher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SupportGroupController extends AbstractController
{
    use ErrorMessageTrait;

    /**
     * Liste des suivis sociaux.
     *
     * @Route("/supports", name="support_index", methods="GET|POST")
     */
    public function index(Request $request, SupportPersonRepository $supportPersonRepo, Pagination $pagination): Response
    {
        $form = $this->createForm(SupportSearchType::class, $search = new SupportSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $supportPersonRepo);
        }

        return $this->renderForm('app/support/support_index.html.twig', [
            'form' => $form,
            'supports' => $pagination->paginate($supportPersonRepo->findSupportsQuery($search), $request),
        ]);
    }

    /**
     * Nouveau suivi social.
     *
     * @Route("/people-group/{id}/new-support", name="people_group_new_support", methods="GET|POST")
     */
    public function new(PeopleGroup $peopleGroup, Request $request, SupportManager $supportManager): Response
    {
        $supportGroup = $supportManager->getNewSupportGroup($peopleGroup, $request);

        if (null === $supportGroup->getService()) {
            $this->addFlash('danger', "Une erreur s'est produite : le nom du service doit être renseigné.");

            return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
        }

        $form = $this->createForm(SupportGroupType::class, $supportGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $supportGroup->getAgreement()
            && $supportManager->create($supportGroup, $form)) {
            return $this->redirectToRoute('support_show', ['id' => $supportGroup->getId()]);
        }

        return $this->render('app/support/support_group_edit.html.twig', [
            'people_group' => $peopleGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Voir un suivi social.
     *
     * @Route("/support/{id}/show", name="support_show", methods="GET")
     */
    public function show(int $id, SupportManager $supportManager, SupportChecker $supportChecker,
    SupportCollections $supportCollections): Response
    {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $supportChecker->check($supportGroup);

        return $this->render('app/support/support_group_view.html.twig', [
            'support' => $supportGroup,
            'referents' => $supportCollections->getReferents($supportGroup),
            'count_rdvs' => $nbRdvs = $supportCollections->getNbRdvs($supportGroup),
            'count_tasks' => $supportCollections->getNbTasks($supportGroup),
            'count_notes' => $supportCollections->getNbNotes($supportGroup),
            'count_documents' => $supportCollections->getNbDocuments($supportGroup),
            'count_payments' => $supportCollections->getNbPayments($supportGroup),
            'last_rdv' => $nbRdvs ? $supportCollections->getLastRdvs($supportGroup) : null,
            'next_rdv' => $nbRdvs ? $supportCollections->getNextRdvs($supportGroup) : null,
            'evaluation' => $supportCollections->getEvaluation($supportGroup),
        ]);
    }

    /**
     * Modification d'un suivi social.
     *
     * @Route("/support/{id}/edit", name="support_edit", methods="GET|POST")
     */
    public function edit(int $id, Request $request, SupportGroupRepository $supportGroupRepo,
        SupportManager $supportManager): Response
    {
        $supportGroup = $supportGroupRepo->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // Récupère l'intervenant social (actuel) avant la mise à jour du formulaire.
        $currentReferent = $supportGroup->getReferent();

        $form = $this->createForm(SupportGroupType::class, $supportGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supportManager->update($supportGroup, $currentReferent);

            $this->addFlash('success', 'Le suivi social est mis à jour.');
        }

        $coefForm = $this->createForm(SupportCoefficientType::class, $supportGroup);

        $addPersonForm = $this->createForm(AddPersonToSupportType::class, null, [
            'attr' => ['supportGroup' => $supportGroup],
        ]);

        return $this->renderForm('app/support/support_group_edit.html.twig', [
            'form' => $form,
            'coef_form' => $coefForm,
            'addPersonForm' => $addPersonForm,
        ]);
    }

    /**
     * Supprime le suivi social du groupe.
     *
     * @Route("/support/{id}/delete", name="support_delete", methods="GET")
     * @IsGranted("DELETE", subject="supportGroup")
     */
    public function delete(SupportGroup $supportGroup, EntityManagerInterface $em): Response
    {
        $em->getFilters()->disable('softdeleteable');
        $em->remove($supportGroup);
        $em->flush();

        SupportManager::deleteCacheItems($supportGroup);

        $this->addFlash('warning', 'Le suivi social est supprimé.');

        return $this->redirectToRoute('people_group_show', ['id' => $supportGroup->getPeopleGroup()->getId()]);
    }

    /**
     * Donne le formulaire pour créer un nouveau suivi social au groupe (via AJAX).
     *
     * @Route("/group/{id}/new_support", name="group_new_support", methods="GET")
     * @Route("support/switch_service", name="support_switch_service", methods="POST")
     */
    public function newSupportGroupAjax(Request $request,
        SupportPersonRepository $supportPersonRepo, ?PeopleGroup $peopleGroup = null): JsonResponse
    {
        $form = $this->createForm(NewSupportGroupType::class, new SupportGroup())
            ->handleRequest($request);

        return $this->json([
            'html' => $this->renderForm('app/support/_partials/_support_new_form.html.twig', [
                'form' => $form,
                'people_group' => $peopleGroup,
                'nb_supports' => $peopleGroup ? $supportPersonRepo->countSupportsOfPeople($peopleGroup) : null,
            ]),
        ]);
    }

    /**
     * Modifier le coefficient du suivi.
     *
     * @Route("/support/{id}/edit-coefficient", name="support_edit_coefficient", methods="POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editCoefficient(int $id, Request $request, SupportGroupRepository $supportGroupRepo,
        EntityManagerInterface $em): Response
    {
        $supportGroup = $supportGroupRepo->findSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $coefForm = $this->createForm(SupportCoefficientType::class, $supportGroup)
            ->handleRequest($request);

        if ($coefForm->isSubmitted() && $coefForm->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le coefficient du suivi est mis à jour.');

            SupportManager::deleteCacheItems($supportGroup);
        }

        return $this->redirectToRoute('support_show', ['id' => $supportGroup->getId()]);
    }

    /**
     * Affiche les suivis dans le mois.
     *
     * @Route("/supports/current_month", name="supports_current_month", methods="GET|POST")
     * @Route("/supports/{year}/{month}", name="supports_in_month", methods="GET|POST", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * })
     */
    public function showSupportsWithPayment(
        int $year = null,
        int $month = null,
        Request $request,
        SupportGroupRepository $supportGroupRepo,
        PaymentRepository $paymentRepo,
        Pagination $pagination
    ): Response {
        $search = new SupportsInMonthSearch();

        /** @var User $user */
        $user = $this->getUser();

        if (User::STATUS_SOCIAL_WORKER === $user->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = $this->createForm(SupportsInMonthSearchType::class, $search)
            ->handleRequest($request);

        $calendar = new Calendar($year, $month);

        $supports = $pagination->paginate(
            $supportGroupRepo->findSupportsBetween(
                $calendar->getFirstDayOfTheMonth(),
                $calendar->getLastDayOfTheMonth(),
                $search
            ),
            $request,
            30
        );

        $supportsId = [];
        foreach ($supports->getItems() as $support) {
            $supportsId[] = $support->getId();
        }

        return $this->renderForm('app/support/supports_in_month_with_payments.html.twig', [
            'calendar' => $calendar,
            'form' => $form,
            'supports' => $supports,
            'payments' => $paymentRepo->findPaymentsBetween(
                $calendar->getFirstDayOfTheMonth(),
                $calendar->getLastDayOfTheMonth(),
                $supportsId
            ),
        ]);
    }

    /**
     * Crée une copie d'un suivi social.
     *
     * @Route("/support/{id}/clone", name="support_clone", methods="GET")
     */
    public function clone(SupportGroup $supportGroup, SupportDuplicator $supportDuplicator): RedirectResponse
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        if ($supportDuplicator->duplicate($supportGroup)) {
            $this->addFlash('success', 'Les informations du précédent suivi ont été ajoutées (évaluation sociale, documents...)');

            SupportManager::deleteCacheItems($supportGroup);
        } else {
            $this->addFlash('warning', 'Aucun autre suivi n\'a été trouvé.');
        }

        return $this->redirectToRoute('support_show', ['id' => $supportGroup->getId()]);
    }

    /**
     * @Route("/supports/switch-referent", name="supports_switch_referent")
     * @IsGranted("ROLE_ADMIN")
     */
    public function switchReferent(Request $request, SupportReferentSwitcher $supportReferentSwitcher): Response
    {
        $form = $this->createForm(SwitchSupportReferentType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $count = $supportReferentSwitcher->switch(
                $form->get('_oldReferent')->getData(),
                $newReferent = $form->get('_newReferent')->getData(),
            );

            if ($count > 0) {
                $this->addFlash('success', $count." suivis ont été transférés 
                    vers {$newReferent->getFullname()}.");
            } else {
                $this->addFlash('warning', "Aucun suivi n'a été transféré.");
            }

            return $this->redirectToRoute('supports_switch_referent');
        }

        return $this->render('app/support/switch_support_referent.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(SupportSearch $search, SupportPersonRepository $supportPersonRepo): Response
    {
        $supports = $supportPersonRepo->findSupportsToExport($search);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('support_index');
        }

        return (new SupportPersonExport())->exportData($supports);
    }
}
