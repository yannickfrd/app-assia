<?php

namespace App\Controller;

use App\Entity\Device;
use App\Form\Service\DeviceType;
use App\Repository\DeviceRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * Affiche la liste des dispositifs
     * 
     * @Route("/admin/devices", name="admin_devices")
     * @param Request $request
     * @param Pagination $pagination
     * @return Response
     */
    public function listDevice(Request $request, Pagination $pagination): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $devices = $pagination->paginate($this->repo->findAllDevicesQuery(), $request);

        return $this->render("app/admin/listDevices.html.twig", [
            "devices" => $devices ?? null
        ]);
    }

    /**
     * Nouveau dispositif
     * 
     * @Route("/admin/device/new", name="admin_device_new", methods="GET|POST")
     * @param Device $device
     * @param Request $request
     * @return Response
     */
    public function newDevice(Device $device = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $device = new Device();

        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
        }

        return $this->render("app/admin/device.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un dispositif
     * 
     * @Route("/admin/device/{id}", name="admin_device_edit", methods="GET|POST")
     * @param Device $device
     * @param Request $request
     * @return Response
     */
    public function editDevice(Device $device, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->updateDevice($device);
        }
        return $this->render("app/admin/device.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Crée un dispositif
     *
     * @param Device $device
     */
    protected function createDevice(Device $device)
    {
        $now = new \DateTime();

        $device->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($device);
        $this->manager->flush();

        $this->addFlash("success", "Le dispositif a été créé.");

        return $this->redirectToRoute("admin_devices");
    }

    /**
     * Met à jour un dispositif
     *
     * @param Device $device
     */
    protected function updateDevice(Device $device)
    {
        $device->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
        return $this->redirectToRoute("admin_devices");
    }
}
