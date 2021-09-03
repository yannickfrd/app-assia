<?php

namespace App\Controller\Support;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Event\Support\SupportGroupEvent;
use App\Form\Model\Support\SupportSearch;
use App\Form\Model\Support\SupportsInMonthSearch;
use App\Form\Support\Support\NewSupportGroupType;
use App\Form\Support\Support\SupportCoefficientType;
use App\Form\Support\Support\SupportGroupType;
use App\Form\Support\Support\SupportSearchType;
use App\Form\Support\Support\SupportsInMonthSearchType;
use App\Repository\Support\PaymentRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Calendar;
use App\Service\Export\SupportPersonExport;
use App\Service\Grammar;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportCollections;
use App\Service\SupportGroup\SupportCreator;
use App\Service\SupportGroup\SupportDuplicator;
use App\Service\SupportGroup\SupportManager;
use App\Service\SupportGroup\SupportPeopleAdder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    use ErrorMessageTrait;

    private $supportPersonRepo;
    private $manager;

    public function __construct(SupportPersonRepository $supportPersonRepo, EntityManagerInterface $manager)
    {
        $this->supportPersonRepo = $supportPersonRepo;
        $this->manager = $manager;
    }

    /**
     * Liste des suivis sociaux.
     *
     * @Route("/supports", name="supports", methods="GET|POST")
     */
    public function viewListSupports(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(SupportSearchType::class, $search = new SupportSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $this->supportPersonRepo);
        }

        return $this->render('app/support/listSupports.html.twig', [
            'form' => $form->createView(),
            'supports' => $pagination->paginate($this->supportPersonRepo->findSupportsQuery($search), $request),
        ]);
    }

    /**
     * Nouveau suivi social.
     *
     * @Route("/group/{id}/support/new", name="support_new", methods="GET|POST")
     */
    public function newSupportGroup(
        PeopleGroup $peopleGroup,
        Request $request,
        SupportCreator $supportCreator,
        EventDispatcherInterface $dispatcher
    ): Response {
        $supportGroup = $supportCreator->getNewSupportGroup($peopleGroup, $request);

        $form = $this->createForm(SupportGroupType::class, $supportGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $supportGroup->getAgreement()) {
            if ($supportCreator->create($supportGroup)) {
                $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.before_create');

                $this->manager->flush();

                $this->addFlash('success', 'Le suivi social est créé.');

                $dispatcher->dispatch(new SupportGroupEvent($supportGroup, $form), 'support.after_create');

                return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
            }
            $this->addFlash('danger', 'Attention, un suivi social est déjà en cours pour '.(
                count($peopleGroup->getPeople()) > 1 ? 'ce ménage.' : 'cette personne.'));
        }

        return $this->render('app/support/supportGroupEdit.html.twig', [
            'people_group' => $peopleGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Donne le formulaire pour créer un nouveau suivi social au groupe (via AJAX).
     *
     * @Route("/group/{id}/new_support", name="group_new_support", methods="GET")
     * @Route("support/switch_service", name="support_switch_service", methods="POST")
     */
    public function newSupportGroupAjax(PeopleGroup $peopleGroup = null, Request $request)
    {
        $form = $this->createForm(NewSupportGroupType::class, new SupportGroup())
            ->handleRequest($request);

        return $this->json([
            'html' => $this->render('app/support/_shared/_newSupportForm.html.twig', [
                'form' => $form->createView(),
                'people_group' => $peopleGroup,
                'nb_supports' => $peopleGroup ? $this->supportPersonRepo->countSupportsOfPeople($peopleGroup) : null,
            ]),
        ]);
    }

    /**
     * Modification d'un suivi social.
     *
     * @Route("/support/{id}/edit", name="support_edit", methods="GET|POST")
     */
    public function editSupportGroup(
        int $id,
        Request $request,
        SupportGroupRepository $supportGroupRepo,
        EventDispatcherInterface $dispatcher
    ): Response {
        $supportGroup = $supportGroupRepo->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(SupportGroupType::class, $supportGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.before_update');

            $this->manager->flush();

            $this->addFlash('success', 'Le suivi social est mis à jour.');

            $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.after_update');
        }

        $formCoeff = $this->createForm(SupportCoefficientType::class, $supportGroup)
            ->handleRequest($request);

        return $this->render('app/support/supportGroupEdit.html.twig', [
            'form' => $form->createView(),
            'formCoeff' => $formCoeff->createView(),
        ]);
    }

    /**
     * Modifier le coefficient du suivi.
     *
     * @Route("/support/{id}/edit-coefficient", name="support_edit_coefficient", methods="POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editCoefficient(
        int $id,
        Request $request,
        SupportGroupRepository $supportGroupRepo,
        EventDispatcherInterface $dispatcher
    ): Response {
        $supportGroup = $supportGroupRepo->findSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $formCoeff = $this->createForm(SupportCoefficientType::class, $supportGroup)
            ->handleRequest($request);

        if ($formCoeff->isSubmitted() && $formCoeff->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Le coefficient du suivi est mis à jour.');

            $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.after_update');
        }

        return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
    }

    /**
     * Voir un suivi social.
     *
     * @Route("/support/{id}/view", name="support_view", methods="GET")
     */
    public function viewSupportGroup(
        int $id,
        SupportManager $supportManager,
        SupportCollections $supportCollections,
        EventDispatcherInterface $dispatcher
    ): Response {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.view');

        return $this->render('app/support/supportGroupView.html.twig', [
            'support' => $supportGroup,
            'referents' => $supportCollections->getReferents($supportGroup),
            'nbNotes' => $supportCollections->getNbNotes($supportGroup),
            'nbRdvs' => $nbRdvs = $supportCollections->getNbRdvs($supportGroup),
            'nbDocuments' => $supportCollections->getNbDocuments($supportGroup),
            'nbPayments' => $supportCollections->getNbPayments($supportGroup),
            'lastRdv' => $nbRdvs ? $supportCollections->getLastRdvs($supportGroup) : null,
            'nextRdv' => $nbRdvs ? $supportCollections->getNextRdvs($supportGroup) : null,
            'evaluation' => $supportCollections->getEvaluation($supportGroup),
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

        (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->deleteItem(
            PeopleGroup::CACHE_GROUP_SUPPORTS_KEY.$peopleGroup->getId());

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Ajout de nouvelles personnes au suivi.
     *
     * @Route("/support/{id}/add_people", name="support_add_people", methods="GET")
     */
    public function addPeopleInSupport(
        SupportGroup $supportGroup,
        SupportPeopleAdder $supportPeopleAdder,
        EventDispatcherInterface $dispatcher
    ): Response {
        if ($supportPeopleAdder->addPeopleInSupport($supportGroup)) {
            $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.before_update');

            $this->manager->flush();

            $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.after_update');
        } else {
            $this->addFlash('warning', "Aucune personne n'a été ajoutée au suivi.");
        }

        return $this->redirectToRoute('support_edit', ['id' => $supportGroup->getId()]);
    }

    /**
     * Retire la personne du suivi social.
     *
     * @Route("/supportGroup/{id}/remove-{support_pers_id}/{_token}", name="remove_support_pers", methods="GET")
     * @ParamConverter("supportPerson", options={"id" = "support_pers_id"})
     */
    public function removeSupportPerson(
        SupportGroup $supportGroup,
        string $_token,
        SupportPerson $supportPerson,
        EventDispatcherInterface $dispatcher
    ): Response {
        // Vérifie si le token est valide avant de retirer la personne du suivi social
        if (!$this->isCsrfTokenValid('remove'.$supportPerson->getId(), $_token)) {
            return $this->getErrorMessage();
        }
        // Vérifie si la personne est le demandeur principal
        if ($supportPerson->getHead()) {
            return $this->json([
                'alert' => 'danger',
                'msg' => 'Le demandeur principal ne peut pas être retiré du suivi.',
            ]);
        }
        // Vérifie si le nombre de personne dans le suivi est supérieur à 1
        if ($supportGroup->getSupportPeople()->count() <= 1) {
            return $this->json([
                'alert' => 'danger',
                'msg' => 'Le suivi doit être constitué d\'au moins une personne.',
            ]);
        }

        try {
            $supportGroup->removeSupportPerson($supportPerson);

            $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.before_update');

            $this->manager->flush();

            $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.after_update');

            return $this->json([
                'action' => 'delete',
                'alert' => 'warning',
                'msg' => $supportPerson->getPerson()->getFullname().' est retiré'.
                    Grammar::gender($supportPerson->getPerson()->getGender()).' du suivi social.',
            ]);
        } catch (\Exception $e) {
            return $this->getErrorMessage();
        }
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
        if (User::STATUS_SOCIAL_WORKER === $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = $this->createForm(SupportsInMonthSearchType::class, $search)
            ->handleRequest($request);

        $calendar = new Calendar($year, $month);

        $supports = $pagination->paginate(
            $supportGroupRepo->findSupportsBetween($calendar->getFirstDayOfTheMonth(), $calendar->getLastDayOfTheMonth(), $search),
            $request, 30);

        $supportsId = [];
        foreach ($supports->getItems() as $support) {
            $supportsId[] = $support->getId();
        }

        return $this->render('app/support/supportsInMonthWithPayments.html.twig', [
            'calendar' => $calendar,
            'form' => $form->createView(),
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
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function cloneSupport(SupportGroup $supportGroup, SupportDuplicator $supportDuplicator, EventDispatcherInterface $dispatcher): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        if ($supportDuplicator->duplicate($supportGroup)) {
            $this->addFlash('success', 'Les informations du précédent suivi ont été ajoutées (évaluation sociale, documents...)');
            $this->manager->flush();

            $dispatcher->dispatch(new SupportGroupEvent($supportGroup), 'support.after_update');
        } else {
            $this->addFlash('warning', 'Aucun autre suivi n\'a été trouvé.');
        }

        return $this->redirectToRoute('support_view', [
            'id' => $supportGroup->getId(),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(SupportSearch $search)
    {
        set_time_limit(10 * 60);

        $supports = $this->supportPersonRepo->findSupportsToExport($search);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new SupportPersonExport())->exportData($supports);
    }
}
