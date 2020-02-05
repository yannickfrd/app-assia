<?php

namespace App\Controller;

use App\Entity\Device;
use App\Form\Service\DeviceType;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeviceController extends AbstractController
{
    private $manager;
    private $currentUser;
    private $repo;

    public function __construct(EntityManagerInterface $manager, Security $security, DeviceRepository $repo)
    {
        $this->manager = $manager;
        $this->currentUser = $security->getUser();
        $this->repo = $repo;
    }

    /**
     * Affiche la liste des dispositifs
     * 
     * @Route("/admin/devices", name="admin_devices")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function listDevice(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $devices =  $paginator->paginate(
            $this->repo->findAllDevicesQuery(),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $devices->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

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
     * @return void
     */
    protected function createDevice(Device $device)
    {
        $now = new \DateTime();

        $device->setCreatedAt($now)
            ->setCreatedBy($this->currentUser)
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->currentUser);

        $this->manager->persist($device);
        $this->manager->flush();

        $this->addFlash("success", "Le dispositif a été créé.");

        return $this->redirectToRoute("admin_devices");
    }

    /**
     * Met à jour un dispositif
     *
     * @param Device $device
     * @return void
     */
    protected function updateDevice(Device $device)
    {
        $device->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->currentUser);

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
        return $this->redirectToRoute("admin_devices");
    }
}
