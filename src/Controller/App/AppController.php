<?php

namespace App\Controller\App;

use App\Form\Admin\SupportsByUserSearchType;
use App\Form\Model\Support\SupportsByUserSearch;
use App\Service\GlossaryService;
use App\Service\Indicators\IndicatorsService;
use App\Service\Indicators\SupportsByUserIndicators;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    protected $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Page d'accueil / Tableau de bord.
     *
     * @Route("/home", name="home", methods="GET")
     * @Route("/", name="index", methods="GET")
     * @IsGranted("ROLE_USER")
     */
    public function home(IndicatorsService $indicators): Response
    {
        return $this->render('app/admin/home/dashboard.html.twig', [
            'indicators' => $this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getIndicators() : null,
            'servicesIndicators' => $indicators->getServicesIndicators($indicators->getUserServices($this->getUser())),
            'supports' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getUserSupports($this->getUser()) : null,
            'notes' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getUserNotes($this->getUser()) : null,
            'rdvs' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getUserRdvs($this->getUser()) : null,
        ]);
    }

    /**
     * Page d'administration de l'application.
     *
     * @Route("/admin", name="admin", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function admin(): Response
    {
        return $this->render('app/admin/admin.html.twig');
    }

    /**
     * Page de gestion du ou des services.
     *
     * @Route("/managing", name="managing", methods="GET")
     * @IsGranted("ROLE_USER")
     */
    public function managing(): Response
    {
        return $this->render('app/admin/managing/managing.html.twig');
    }

    /**
     * Vide le cache.
     *
     * @Route("/admin/cache/clear", name="cache_clear", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function clearCache(): Response
    {
        $this->cache->clear();

        $this->addFlash('success', 'Le cache est vidé.');

        return $this->redirectToRoute('home');
    }

    /**
     * Tableau de bord du/des services.
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

    /**
     * PHP Info.
     *
     * @Route("/admin/phpinfo", name="admin_phpinfo", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function getPhpInfo(): Response
    {
        if (1 != $this->getUser()->getId()) {
            return $this->redirect('home');
        }

        return new Response(phpinfo());
    }

    /**
     * Glossaire de toutes les variables.
     *
     * @Route("/admin/glossary", name="admin_glossary", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function glossary(GlossaryService $glossary): Response
    {
        return $this->render('app/admin/glossary.html.twig', [
            'entities' => $glossary->getAll(),
        ]);
    }
}
