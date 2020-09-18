<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\Service;
use App\Form\Device\DeviceType;
use App\Repository\DeviceRepository;
use App\Repository\SubServiceRepository;
use App\Repository\UserRepository;
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

    /**
     * Donne les dispositifs rattachés au service.
     *
     * @ROute("/service/{id}/devices", name="service_devices", methods="GET")
     */
    public function getDevicesFromService(Service $service, SubServiceRepository $repoSubService, DeviceRepository $repoDevice, UserRepository $repoUser)
    {
        $subServices = [];
        foreach ($repoSubService->getSubServicesFromService($service) as $subService) {
            $subServices[$subService->getId()] = $subService->getName();
        }

        $devices = [];
        foreach ($repoDevice->getDevicesFromService($service->getId()) as $device) {
            $devices[$device->getId()] = $device->getName();
        }

        $users = [];
        foreach ($repoUser->getUsersFromService($service) as $user) {
            $users[$user->getId()] = $user->getFullname();
        }

        return $this->json([
            'subServices' => $subServices,
            'devices' => $devices,
            'users' => $users,
        ], 200);
    }

    /**
     * Désactive ou réactive le dispositif.
     *
     * @Route("/device/{id}/disable", name="admin_device_disable", methods="GET")
     */
    public function disableDevice(Device $device): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($device->getDisabledAt()) {
            $device->setDisabledAt(null);
            $this->addFlash('success', 'Le dispositif est réactivé.');
        } else {
            $device->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le dispositif est désactivé.');
        }

        $this->manager->flush();

        return $this->redirectToRoute('admin_device_edit', ['id' => $device->getId()]);
    }
}
