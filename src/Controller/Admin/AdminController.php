<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Organization\User;
use App\Service\GlossaryService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AdminController extends AbstractController
{
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
     * @Route("/admin/cache/clear", name="cache_clear", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function clearCache(): Response
    {
        (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->clear();

        $this->addFlash('success', 'Le cache est vidé.');

        return $this->redirectToRoute('home');
    }

    /**
     * Glossaire de toutes les variables de l'application.
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

    /**
     * @Route("/admin/phpinfo", name="admin_phpinfo", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function phpInfo(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (1 != $user->getId()) {
            return $this->redirect('home');
        }

        return new Response(phpinfo());
    }

    /**
     * @Route("/admin/apcu-cache/clear", name="apcu_cache_clear", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function clearApcuCache(): Response
    {
        apcu_clear_cache();

        $this->addFlash('success', 'APCU Cache was cleared.');

        return $this->redirectToRoute('home');
    }
}
