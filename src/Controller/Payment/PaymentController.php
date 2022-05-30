<?php

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\User;
use App\Entity\Support\Payment;
use App\Form\Model\Support\PaymentSearch;
use App\Form\Model\Support\SupportPaymentSearch;
use App\Form\Support\Payment\PaymentSearchType;
use App\Form\Support\Payment\PaymentType;
use App\Form\Support\Payment\SupportPaymentSearchType;
use App\Repository\Support\PaymentRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Export\HotelContributionlExport;
use App\Service\Export\PaymentAccountingExport;
use App\Service\Export\PaymentFullExport;
use App\Service\Indicators\PaymentIndicators;
use App\Service\Pagination;
use App\Service\Payment\PaymentManager;
use App\Service\SupportGroup\SupportManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PaymentController extends AbstractController
{
    use ErrorMessageTrait;

    /**
     * Liste des participations financières.
     *
     * @Route("/payments", name="payments_index", methods="GET|POST")
     */
    public function index(Request $request, Pagination $pagination, PaymentRepository $paymentRepo): Response
    {
        $form = $this->createForm(PaymentSearchType::class, $search = new PaymentSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search, $paymentRepo);
        }
        if ($request->query->get('export-accounting')) {
            return $this->exportAccountingData($search, $paymentRepo);
        }
        if ($request->query->get('export-delta')) {
            return $this->exportDeltaData($search, $paymentRepo);
        }

        return $this->render('app/payment/payment_index.html.twig', [
            'form' => $form->createView(),
            'payments' => $pagination->paginate(
                $paymentRepo->findPaymentsQuery($search),
                $request,
                20
            ),
        ]);
    }

    /**
     * Liste des participations financières du suivi social.
     *
     * @Route("/support/{id}/payments", name="support_payments_index", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function indexSupportPayments(
        int $id,
        PaymentRepository $paymentRepo,
        SupportManager $supportManager,
        Request $request,
        Pagination $pagination
    ): Response {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportPaymentSearchType::class, $search = new SupportPaymentSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search, $paymentRepo, $supportGroup);
        }

        $payment = (new Payment())->setSupportGroup($supportGroup);

        $form = $this->createForm(PaymentType::class, $payment);

        return $this->render('app/payment/support_payment_index.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nbTotalPayments' => $request->query->count() ? $paymentRepo->count(['supportGroup' => $supportGroup]) : null,
            'payments' => $pagination->paginate(
                $paymentRepo->findPaymentsOfSupportQuery($supportGroup, $search),
                $request,
                200
            ),
        ]);
    }

    /**
     * Nouvelle participation financière.
     *
     * @Route("/support/{id}/payment/create", name="payment_create", methods="POST")
     */
    public function create(
        int $id,
        Request $request,
        SupportGroupRepository $groupRepo,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): JsonResponse {
        $supportGroup = $groupRepo->findSupportById($id);
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(PaymentType::class, $payment = new Payment())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment->setSupportGroup($supportGroup);

            $em->persist($payment);
            $em->flush();

            PaymentManager::deleteCacheItems($payment);

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => $translator->trans('payment.created_successfully', [
                    '%payment_type%' => $payment->getTypeToString(),
                ], 'app'),
                'payment' => $payment,
            ], 200, [], ['groups' => Payment::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Obtenir la redevance.
     *
     * @Route("/payment/{id}/show", name="payment_show", methods="GET")
     */
    public function show(Payment $payment): JsonResponse
    {
        $this->denyAccessUnlessGranted('VIEW', $payment);

        return $this->json([
            'action' => 'show',
            'payment' => $payment,
        ], 200, [], ['groups' => Payment::SERIALIZER_GROUPS]);
    }

    /**
     * Modification d'une participation financière.
     *
     * @Route("/payment/{id}/edit", name="payment_edit", methods="POST")
     */
    public function edit(
        Payment $payment,
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): JsonResponse {
        $this->denyAccessUnlessGranted('EDIT', $payment);

        $form = $this->createForm(PaymentType::class, $payment)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            PaymentManager::deleteCacheItems($payment);

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => $translator->trans('payment.updated_successfully', [
                    '%payment_type%' => $payment->getTypeToString(),
                ], 'app'),
                'payment' => $payment,
            ], 200, [], ['groups' => Payment::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Supprime la participation financière.
     *
     * @Route("/payment/{id}/delete", name="payment_delete", methods="GET")
     */
    public function delete(Payment $payment, EntityManagerInterface $em, TranslatorInterface $translator): JsonResponse
    {
        $paymentId = $payment->getId();

        $this->denyAccessUnlessGranted('DELETE', $payment);

        $em->remove($payment);
        $em->flush();

        PaymentManager::deleteCacheItems($payment);

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $translator->trans('payment.deleted_successfully', [
                '%payment_type%' => $payment->getTypeToString(),
            ], 'app'),
            'payment' => ['id' => $paymentId],
        ]);
    }

    /**
     * @Route("/payment/{id}/restore", name="payment_restore", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function restore(
        int $id,
        PaymentRepository $paymentRepo,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): JsonResponse {
        $payment = $paymentRepo->findPayment($id, true);

        $payment->setDeletedAt(null);
        $em->flush();

        PaymentManager::deleteCacheItems($payment);

        return $this->json([
            'action' => 'restore',
            'alert' => 'success',
            'msg' => $translator->trans('payment.restored_successfully', [
                '%payment_type%' => $payment->getTypeToString(),
            ], 'app'),
            'payment' => ['id' => $id],
        ]);
    }

    /**
     * Affiche les indicateurs mensuels des participations financières.
     *
     * @Route("/payment/indicators", name="payment_indicators", methods="GET|POST")
     */
    public function showPaymentIndicators(
        Request $request,
        PaymentRepository $paymentRepo,
        PaymentIndicators $indicators
    ): Response {
        $search = $this->getPaymentSearch();

        $form = $this->createForm(PaymentSearchType::class, $search)
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search, $paymentRepo);
        }

        $datas = $indicators->getIndicators(
            $paymentRepo->findPaymentsForIndicators($search),
            $search
        );

        return $this->render('app/payment/payment_indicators.html.twig', [
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
    protected function exportFullData(
        $search,
        PaymentRepository $paymentRepo,
        $supportGroup = null,
        UrlGeneratorInterface $router = null
    ): Response {
        $payments = $paymentRepo->findPaymentsToExport($search, $supportGroup);

        if (!$payments) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('payments_index');
        }

        return (new PaymentFullExport($router))->exportData($payments);
    }

    /**
     * Exporte les données.
     *
     * @return Response|RedirectResponse
     */
    protected function exportAccountingData(
        PaymentSearch $search,
        PaymentRepository $paymentRepo,
        UrlGeneratorInterface $router = null
    ): Response {
        $payments = $paymentRepo->findPaymentsToExport($search);

        if (!$payments) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('payments_index');
        }

        return (new PaymentAccountingExport($router))->exportData($payments);
    }

    /**
     * Exporte les données.
     *
     * @return Response|RedirectResponse
     */
    protected function exportDeltaData(
        PaymentSearch $search,
        PaymentRepository $paymentRepo,
        UrlGeneratorInterface $router = null
    ): Response {
        $payments = $paymentRepo->findHotelContributionsToExport($search);

        if (!$payments) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('payments_index');
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
