<?php

declare(strict_types=1);

namespace App\Controller\App;

use App\Entity\Organization\User;
use App\Form\Admin\SupportsByUserSearchType;
use App\Form\Model\Support\SupportsByUserSearch;
use App\Service\Indicators\IndicatorsService;
use App\Service\Indicators\SupportsByUserIndicators;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AppController extends AbstractController
{
    /**
     * Page d'accueil / Tableau de bord.
     *
     * @Route("/home", name="home", methods="GET")
     * @Route("/", name="index", methods="GET")
     */
    public function home(IndicatorsService $indicators): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('app/home/dashboard.html.twig', [
            'indicators' => $this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getIndicators() : null,
            'services_indicators' => $indicators->getServicesIndicators($indicators->getUserServices($user)),
            'supports' => $indicators->getUserSupports($user),
            'notes' => $indicators->getUserNotes($user),
            'rdvs' => $indicators->getUserRdvs($user),
            'tasks' => $indicators->getUserTasks($user),
        ]);
    }

    /**
     * Page de gestion du ou des services.
     *
     * @Route("/managing", name="managing", methods="GET")
     */
    public function managing(): Response
    {
        return $this->render('app/admin/managing/managing.html.twig');
    }

    /**
     * Page de répartition des suivis par travailleur social.
     *
     * @Route("/dashboard/supports_by_user", name="supports_by_user", methods="GET")
     */
    public function showSupportsByUser(SupportsByUserIndicators $indicators, SupportsByUserSearch $search, Request $request): Response
    {
        $form = $this->createForm(SupportsByUserSearchType::class, $search)
            ->handleRequest($request);

        return $this->render('app/admin/dashboard/supportsByUser.html.twig', [
            'form' => $form->createView(),
            'datas' => $form->isSubmitted() || !$this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getSupportsbyDevice($search) : null,
        ]);
    }
}
