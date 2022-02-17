<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Setting;
use App\Form\Admin\SettingType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminSettingController extends AbstractController
{
    /**
     * @Route("/admin/settings", name="admin_settings", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function adminSettings(Request $request, EntityManagerInterface $em): Response
    {
        $setting = $em->getRepository(Setting::class)->findOneBy([]) ?? new Setting();

        $form = $this->createForm(SettingType::class, $setting)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($setting);
            $em->flush();

            $this->addFlash('success', 'La configuration est bien enregistrée.');
        }

        return $this->render('app/admin/settings.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
