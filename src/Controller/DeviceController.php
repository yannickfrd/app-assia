<?php

namespace App\Controller;

use App\Entity\Device;
use App\Form\Device\DeviceType;
use App\Repository\DeviceRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeviceController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, DeviceRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Affiche la liste des dispositifs.
     *
     * @Route("/admin/devices", name="admin_devices", methods="GET")
     */
    public function listDevice(Request $request, Pagination $pagination): Response
    {
        return $this->render('app/device/listDevices.html.twig', [
            'devices' => $pagination->paginate($this->repo->findAllDevicesQuery(), $request) ?? null,
        ]);
    }

    /**
     * Nouveau dispositif.
     *
     * @Route("/admin/device/new", name="admin_device_new", methods="GET|POST")
     */
    public function newDevice(Device $device = null, Request $request): Response
    {
        $device = new Device();

        $form = ($this->createForm(DeviceType::class, $device))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($device);
            $this->manager->flush();

            $this->addFlash('success', 'Le dispositif est créé.');

            return $this->redirectToRoute('admin_devices');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Une erreur s\'est produite.');
        }

        return $this->render('app/device/device.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un dispositif.
     *
     * @Route("/admin/device/{id}", name="admin_device_edit", methods="GET|POST")
     */
    public function editDevice(Device $device, Request $request): Response
    {
        $form = ($this->createForm(DeviceType::class, $device))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        return $this->render('app/device/device.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
