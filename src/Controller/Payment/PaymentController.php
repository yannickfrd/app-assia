<?php

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\User;
use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\PaymentSearch;
use App\Form\Model\Support\SupportPaymentSearch;
use App\Form\Support\Payment\PaymentSearchType;
use App\Form\Support\Payment\PaymentType;
use App\Form\Support\Payment\SupportPaymentSearchType;
use App\Repository\Support\PaymentRepository;
use App\Service\Export\HotelContributionlExport;
use App\Service\Export\PaymentAccountingExport;
use App\Service\Export\PaymentFullExport;
use App\Service\Indicators\PaymentIndicators;
use App\Service\Normalisation;
use App\Service\Pagination;
use App\Service\Payment\PaymentManager;
use App\Service\SupportGroup\SupportManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PaymentController extends AbstractController
{
    use ErrorMessageTrait;

    private $em;
    private $paymentRepo;

    public function __construct(EntityManagerInterface $em, PaymentRepository $paymentRepo)
    {
        $this->em = $em;
        $this->paymentRepo = $paymentRepo;
    }

    /**
     * Liste des participations financières.
     *
     * @Route("/payments", name="payments", methods="GET|POST")
     */
    public function listPayments(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(PaymentSearchType::class, $search = new PaymentSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search);
        }
        if ($request->query->get('export-accounting')) {
            return $this->exportAccountingData($search);
        }
        if ($request->query->get('export-delta')) {
            return $this->exportDeltaData($search);
        }

        return $this->render('app/payment/listPayments.html.twig', [
            'form' => $form->createView(),
            'payments' => $pagination->paginate(
                $this->paymentRepo->findPaymentsQuery($search),
                $request,
                20
            ),
        ]);
    }

    /**
     * Liste des participations financières du suivi social.
     *
     * @Route("/support/{id}/payments", name="support_payments", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function showSupportPayments(
        int $id,
        SupportManager $supportManager,
        Request $request,
        Pagination $pagination): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportPaymentSearchType::class, $search = new SupportPaymentSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search, $supportGroup);
        }

        $payment = (new Payment())
            ->setSupportGroup($supportGroup)
            ->setMonthContrib((new \DateTime())->modify('first day of last month'));

        $form = $this->createForm(PaymentType::class, $payment);

        return $this->render('app/payment/supportPayments.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nbTotalPayments' => $request->query->count() ? $this->paymentRepo->count(['supportGroup' => $supportGroup]) : null,
            'payments' => $pagination->paginate(
                $this->paymentRepo->findPaymentsOfSupportQuery($supportGroup, $search),
                $request,
                200
            ),
        ]);
    }

    /**
     * Nouvelle participation financière.
     *
     * @Route("/support/{id}/payment/new", name="payment_new", methods="POST")
     */
    public function createPayment(SupportGroup $supportGroup, Request $request,NormalizerInterface $normalizer,
        Normalisation $normalisation): JsonResponse
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(PaymentType::class, $payment = new Payment())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment->setSupportGroup($supportGroup);

            $this->em->persist($payment);
            $this->em->flush();

            PaymentManager::deleteCacheItems($payment);

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => 'L\'opération "'.$payment->getTypeToString().'" est enregistrée.',
                'data' => [
                    'payment' => $normalizer->normalize($payment, null, [
                        'groups' => ['get', 'export'],
                    ]),
                ],
            ]);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Obtenir la redevance.
     *
     * @Route("/payment/{id}/get", name="payment_get", methods="GET")
     */
    public function getPayment(int $id, PaymentRepository $paymentRepo): JsonResponse
    {
        $payment = $paymentRepo->findPayment($id);
        $this->denyAccessUnlessGranted('VIEW', $payment);

        return $this->json([
            'action' => 'show',
            'data' => [
                'payment' => $payment,
                'createdBy' => $payment->getCreatedBy()->getFullname(),
                'updatedBy' => $payment->getUpdatedBy()->getFullname(),
            ],
        ], 200, [], ['groups' => ['get', 'view']]);
    }

    /**
     * Modification d'une participation financière.
     *
     * @Route("/payment/{id}/edit", name="payment_edit", methods="POST")
     */
    public function editPayment(
        Payment $payment,
        Request $request,
        NormalizerInterface $normalizer,
        Normalisation $normalisation
    ): JsonResponse {
        $this->denyAccessUnlessGranted('EDIT', $payment);

        $form = $this->createForm(PaymentType::class, $payment)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            PaymentManager::deleteCacheItems($payment);

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => 'L\'opération "'.$payment->getTypeToString().'" est modifiée.',
                'data' => [
                    'payment' => $normalizer->normalize($payment, null, [
                        'groups' => ['get', 'export'],
                    ]),
                ],
            ]);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Supprime la participation financière.
     *
     * @Route("/payment/{id}/delete", name="payment_delete", methods="GET")
     */
    public function deletePayment(Payment $payment): JsonResponse
    {
        $this->denyAccessUnlessGranted('DELETE', $payment);

        $this->em->remove($payment);
        $this->em->flush();

        PaymentManager::deleteCacheItems($payment);

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'L\'opération "'.$payment->getTypeToString().'" est supprimée.',
        ]);
    }

    /**
     * @Route("/payment/{id}/restore", name="payment_restore", methods="GET")
     */
    public function restore(
        int $id,
        PaymentRepository $paymentRepo,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): JsonResponse {
        $payment = $paymentRepo->findPayment($id, true);

        $this->denyAccessUnlessGranted('EDIT', $payment->getSupportGroup());

        $payment->setDeletedAt(null);
        $em->flush();

        PaymentManager::deleteCacheItems($payment);

        return $this->json([
            'action' => 'restore',
            'alert' => 'success',
            'msg' => $translator->trans('payment.restored_successfully', [], 'app'),
            'payment' => ['id' => $id],
        ]);
    }

    /**
     * Affiche les indicateurs mensuels des participations financières.
     *
     * @Route("/payment/indicators", name="payment_indicators", methods="GET|POST")
     */
    public function showPaymentIndicators(Request $request, PaymentIndicators $indicators): Response
    {
        $search = $this->getPaymentSearch();

        $form = $this->createForm(PaymentSearchType::class, $search)
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search);
        }

        $datas = $indicators->getIndicators(
            $this->paymentRepo->findPaymentsForIndicators($search),
            $search
        );

        return $this->render('app/payment/paymentIndicators.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
        ]);
    }

    /**
     * Exporte les données.
     *
     * @param PaymentSearch|SupportPaymentSearch $search
     *
     * @return Response|RedirectResponse
     */
    protected function exportFullData($search, $supportGroup = null, UrlGeneratorInterface $router = null): Response
    {
        $payments = $this->paymentRepo->findPaymentsToExport($search, $supportGroup);

        if (!$payments) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('payments');
        }

        return (new PaymentFullExport($router))->exportData($payments);
    }

    /**
     * Exporte les données.
     *
     * @return Response|RedirectResponse
     */
    protected function exportAccountingData(PaymentSearch $search, UrlGeneratorInterface $router = null): Response
    {
        $payments = $this->paymentRepo->findPaymentsToExport($search);

        if (!$payments) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('payments');
        }

        return (new PaymentAccountingExport($router))->exportData($payments);
    }

    /**
     * Exporte les données.
     *
     * @return Response|RedirectResponse
     */
    protected function exportDeltaData(PaymentSearch $search, UrlGeneratorInterface $router = null): Response
    {
        $payments = $this->paymentRepo->findHotelContributionsToExport($search);

        if (!$payments) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('payments');
        }

        return (new HotelContributionlExport($router))->exportData($payments);
    }

    protected function getPaymentSearch(): PaymentSearch
    {
        $today = new \DateTime('today');
        $search = (new PaymentSearch())
            ->setType([1])
            ->setStart(new \DateTime($today->format('Y').'-01-01'))
            ->setEnd($today);

        /** @var User $user */
        $user = $this->getUser();

        if (User::STATUS_SOCIAL_WORKER === $user->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        return $search;
    }
}
