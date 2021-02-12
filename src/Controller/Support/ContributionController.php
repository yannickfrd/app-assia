<?php

namespace App\Controller\Support;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\User;
use App\Entity\Support\Contribution;
use App\Entity\Support\SupportGroup;
use App\EntityManager\SupportManager;
use App\Form\Model\Support\ContributionSearch;
use App\Form\Model\Support\SupportContributionSearch;
use App\Form\Support\Contribution\ContributionSearchType;
use App\Form\Support\Contribution\ContributionType;
use App\Form\Support\Contribution\SupportContributionSearchType;
use App\Notification\MailNotification;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Support\ContributionRepository;
use App\Service\Calendar;
use App\Service\Export\ContributionFullExport;
use App\Service\Export\ContributionLightExport;
use App\Service\ExportPDF;
use App\Service\Indicators\ContributionIndicators;
use App\Service\Normalisation;
use App\Service\Pagination;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Twig\Environment;

class ContributionController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repoContribution;

    public function __construct(EntityManagerInterface $manager, ContributionRepository $repoContribution)
    {
        $this->manager = $manager;
        $this->repoContribution = $repoContribution;
    }

    /**
     * Liste des participations financières.
     *
     * @Route("contributions", name="contributions", methods="GET|POST")
     */
    public function listContributions(Request $request, Pagination $pagination): Response
    {
        $search = new ContributionSearch();
        if (User::STATUS_SOCIAL_WORKER === $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = ($this->createForm(ContributionSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search);
        }
        if ($request->query->get('export2')) {
            return $this->exportLightData($search);
        }

        return $this->render('app/support/contribution/listContributions.html.twig', [
            'form' => $form->createView(),
            'contributions' => $pagination->paginate($this->repoContribution->findContributionsQuery($search), $request, 20) ?? null,
        ]);
    }

    /**
     * Liste des participations financières du suivi social.
     *
     * @Route("support/{id}/contributions", name="support_contributions", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function showSupportContributions(int $id, SupportManager $supportManager, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $search = new SupportContributionSearch();

        $formSearch = ($this->createForm(SupportContributionSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search, $supportGroup);
        }

        $contribution = (new Contribution())
            ->setMonthContrib((new \DateTime())->modify('first day of last month'));

        $form = $this->createForm(ContributionType::class, $contribution);

        // Récupère les contributions en cache.
        // $contributions = (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(SupportGroup::CACHE_SUPPORT_CONTRIBUTIONS_KEY.$supportGroup->getId(),
        //     function (CacheItemInterface $item) use ($supportGroup, $pagination, $search, $request) {
        //         $item->expiresAfter(\DateInterval::createFromDateString('7 hours'));

        //         return $pagination->paginate($this->repoContribution->findContributionsOfSupportQuery($supportGroup->getId(), $search), $request, 200);
        //     }
        // );

        return $this->render('app/support/contribution/supportContributions.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nbTotalContributions' => $request->query->count() ? $this->repoContribution->count(['supportGroup' => $supportGroup]) : null,
            'contributions' => $pagination->paginate($this->repoContribution->findContributionsOfSupportQuery($supportGroup->getId(), $search), $request, 200),
        ]);
    }

    /**
     * Donne les ressources.
     *
     * @Route("support/{id}/resources", name="support_resources", methods="GET")
     *
     * @param int $id // SupportGroup
     */
    public function getResources(int $id, SupportManager $supportManager, PlaceRepository $repoPlace, EvaluationGroupRepository $repoEvaluation)
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $evaluation = $repoEvaluation->findEvaluationResourceById($id);

        $place = $repoPlace->findCurrentPlaceOfSupport($supportGroup);

        $salaryAmt = 0;
        $resourcesAmt = 0;

        if ($evaluation) {
            foreach ($evaluation->getEvaluationPeople() as $evaluationPerson) {
                if ($evaluationPerson->getEvalBudgetPerson()) {
                    $salaryAmt += $evaluationPerson->getEvalBudgetPerson()->getSalaryAmt();
                    $resourcesAmt += $evaluationPerson->getEvalBudgetPerson()->getResourcesAmt();
                }
            }

            $contributionRate = $supportGroup->getService()->getContributionRate();
            $toPayAmt = round($resourcesAmt * $contributionRate * 100) / 100;
        }

        return $this->json([
            'code' => 200,
            'action' => 'get_resources',
            'data' => [
                'salaryAmt' => $salaryAmt,
                'resourcesAmt' => $resourcesAmt,
                'toPayAmt' => $toPayAmt ?? null,
                'contributionAmt' => $evaluation && $evaluation->getEvalBudgetGroup() ? $evaluation->getEvalBudgetGroup()->getContributionAmt() : null,
                'rentAmt' => $place ? $place->getRentAmt() : null,
            ],
        ], 200);
    }

    /**
     * Nouvelle participation financière.
     *
     * @Route("support/{id}/contribution/new", name="contribution_new", methods="POST")
     */
    public function newContribution(SupportGroup $supportGroup, Contribution $contribution = null, Request $request, NormalizerInterface $normalizer, Normalisation $normalisation): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $contribution = (new Contribution())
            ->setSupportGroup($supportGroup);

        $form = ($this->createForm(ContributionType::class, $contribution))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createContribution($supportGroup, $contribution, $normalizer);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Obtenir la redevance.
     *
     * @Route("contribution/{id}/get", name="contribution_get", methods="GET")
     */
    public function getContribution(Contribution $contribution, NormalizerInterface $normalizer): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $contribution);

        return $this->json([
            'code' => 200,
            'action' => 'show',
            'data' => [
                'contribution' => $normalizer->normalize($contribution, null, ['groups' => ['get', 'view']]),
                'createdBy' => $contribution->getCreatedBy()->getFullname(),
                'updatedBy' => $contribution->getUpdatedBy()->getFullname(),
            ],
        ], 200);
    }

    /**
     * Modification d'une participation financière.
     *
     * @Route("contribution/{id}/edit", name="contribution_edit", methods="POST")
     */
    public function editContribution(Contribution $contribution, Request $request, NormalizerInterface $normalizer, Normalisation $normalisation): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $contribution);

        $form = ($this->createForm(ContributionType::class, $contribution))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateContribution($contribution, $normalizer);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Supprime la participation financière.
     *
     * @Route("contribution/{id}/delete", name="contribution_delete", methods="GET")
     */
    public function deleteContribution(Contribution $contribution): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $contribution);

        $this->manager->remove($contribution);
        $this->manager->flush();

        $this->discache($contribution->getSupportGroup());

        return $this->json([
            'code' => 200,
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est supprimée.',
        ], 200);
    }

    /**
     * Export un reçu de paiement au format PDF.
     *
     * @Route("contribution/{id}/export/pdf", name="contribution_export_pdf", methods="GET")
     */
    public function exportPayment(int $id, Request $request, SupportManager $supportManager, ExportPDF $exportPDF, Environment $renderer): Response
    {
        /** @var Contribution $contribution */
        $contribution = $this->repoContribution->findContribution($id);

        $supportGroup = $contribution->getSupportGroup();

        $this->denyAccessUnlessGranted('VIEW', $contribution);

        if (!$contribution->getToPayAmt()) {
            $this->addFlash('danger', 'Le montant à payer est égal à 0.');

            return $this->redirectToRoute('support_contributions', ['id' => $supportGroup->getId()]);
        }

        $title = $contribution->getPaymentDate() ? 'Reçu de paiement' : 'Avis d\'échéance';
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();
        $fullnameSupport = $supportManager->getHeadPersonSupport($supportGroup)->getFullname();

        $content = $renderer->render('app/support/contribution/contributionExport.html.twig', [
            'title' => $title,
            'logo_path' => $exportPDF->getPathImage($logoPath),
            'contribution' => $contribution,
            'support' => $supportGroup,
            'paidAmtToString' => (new \NumberFormatter('fr-FR', \NumberFormatter::SPELLOUT))->format($contribution->getPaidAmt()),
        ]);

        $exportPDF->createDocument($content, $title, $logoPath, $fullnameSupport);

        $contribution->setPdfGenerateAt(new \Datetime());

        $this->manager->flush();

        return $exportPDF->download($request->server->get('HTTP_USER_AGENT') != 'Symfony BrowserKit');
    }

    /**
     * Envoie un email avec le reçu de paiement au format PDF.
     *
     * @Route("contribution/{id}/send/pdf", name="contribution_send_pdf", methods="GET")
     */
    public function sendPaymentByEmail(int $id, SupportManager $supportManager, ExportPDF $exportPDF, Environment $renderer, MailNotification $notification): Response
    {
        /** @var Contribution */
        $contribution = $this->repoContribution->findContribution($id);

        $this->denyAccessUnlessGranted('VIEW', $contribution);

        $supportGroup = $contribution->getSupportGroup();

        $title = 'Reçu de paiement';
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();
        $person = $supportManager->getHeadPersonSupport($supportGroup);
        $fullnameSupport = $person->getFullname();
        $email = $person->getEmail();

        $content = $renderer->render('app/support/contribution/contributionExport.html.twig', [
            'title' => $title,
            'logo_path' => $exportPDF->getPathImage($logoPath),
            'contribution' => $contribution,
            'support' => $supportGroup,
            'paidAmtToString' => (new \NumberFormatter('fr-FR', \NumberFormatter::SPELLOUT))->format($contribution->getPaidAmt()),
        ]);

        if (!$person->getEmail()) {
            return $this->json([
                'code' => 200,
                'action' => 'error',
                'alert' => 'danger',
                'msg' => 'Le suivi n\'a pas d\'adresse e-mail renseignée.',
            ]);
        }

        if (!$contribution->getPaymentDate() || !$contribution->getPaidAmt()) {
            return $this->json([
                'code' => 200,
                'action' => 'error',
                'alert' => 'danger',
                'msg' => 'Il n\'y a pas de paiment enregistré.',
            ]);
        }

        $exportPDF->createDocument($content, $title, $logoPath, $fullnameSupport);

        $path = $exportPDF->save();

        $context = [
            'person' => $person,
            'contribution' => $contribution,
            'support' => $supportGroup,
        ];

        $date = $contribution->getMonthContrib() ? Calendar::MONTHS[(int) $contribution->getMonthContrib()->format('m')].$contribution->getMonthContrib()->format(' Y') : '';
        $notification->send(
            [
                'email' => $email,
                'name' => $fullnameSupport,
            ],
            'ESPERER 95 | '.$title.' '.$date.' | '.$fullnameSupport,
            $renderer->render('emails/receiptPayment.html.twig', $context),
            null,
            null,
            $this->getUser()->getEmail(),
            $this->getUser()->getEmail(),
            [$path],
        );

        $contribution->setMailSentAt(new \Datetime());

        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'send_receipt',
            'alert' => 'success',
            'msg' => "Le reçu du paiement a été envoyé par email à $fullnameSupport.",
        ]);
    }

    /**
     * Affiche les indicateurs mensuels des participations financières.
     *
     * @Route("contribution/indicators", name="contribution_indicators", methods="GET|POST")
     */
    public function showContributionIndicators(Request $request, ContributionIndicators $indicators): Response
    {
        $search = $this->getContributionSearch();

        $form = ($this->createForm(ContributionSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search);
        }

        $datas = $indicators->getIndicators(
            $this->repoContribution->findContributionsForIndicators($search),
            $search,
        );

        return $this->render('app/support/contribution/contributionIndicators.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
        ]);
    }

    /**
     * Exporte les données.
     *
     * @param ContributionSearch|SupportContributionSearch $search
     */
    protected function exportFullData($search, $supportGroup = null, UrlGeneratorInterface $router = null)
    {
        $supports = $this->repoContribution->findContributionsToExport($search, $supportGroup);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new ContributionFullExport($router))->exportData($supports);
    }

    /**
     * Exporte les données.
     */
    protected function exportLightData(ContributionSearch $search, UrlGeneratorInterface $router = null)
    {
        $supports = $this->repoContribution->findContributionsToExport($search);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new ContributionLightExport($router))->exportData($supports);
    }

    /**
     * Crée la contribution une fois le formulaire soumis et validé.
     */
    protected function createContribution(SupportGroup $supportGroup, Contribution $contribution, NormalizerInterface $normalizer): Response
    {
        $supportGroup->setUpdatedAt(new \DateTime());

        $contribution->setStillToPayAmt($contribution->getToPayAmt() - $contribution->getPaidAmt());

        $this->manager->persist($contribution);
        $this->manager->flush();

        $this->discache($supportGroup);

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est enregistrée.',
            'data' => [
                'contribution' => $normalizer->normalize($contribution, null, [
                    'groups' => ['get', 'export'],
                ]),
            ],
        ], 200);
    }

    /**
     * Met à jour la contribution une fois le formulaire soumis et validé.
     */
    protected function updateContribution(Contribution $contribution, NormalizerInterface $normalizer): Response
    {
        $contribution->setStillToPayAmt();
        $contribution->getSupportGroup()->setUpdatedAt(new \DateTime());

        $this->manager->flush();

        $this->discache($contribution->getSupportGroup(), true);

        return $this->json([
            'code' => 200,
            'action' => 'update',
            'alert' => 'success',
            'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est modifiée.',
            'data' => [
                'contribution' => $normalizer->normalize($contribution, null, [
                    'groups' => ['get', 'export'],
                ]),
            ],
        ], 200);
    }

    protected function getContributionSearch()
    {
        $today = new \DateTime('today');
        $search = (new ContributionSearch())
            ->setType([1])
            ->setStart(new \DateTime($today->format('Y').'-01-01'))
            ->setEnd($today);

        if (User::STATUS_SOCIAL_WORKER === $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        return $search;
    }

    /**
     * Supprime les contributions en cache du suivi.
     */
    protected function discache(SupportGroup $supportGroup, $isUpdate = false): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if (false === $isUpdate) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_CONTRIBUTIONS_KEY.$supportGroup->getId());
        }

        return $cache->deleteItem(SupportGroup::CACHE_SUPPORT_CONTRIBUTIONS_KEY.$supportGroup->getId());
    }
}
