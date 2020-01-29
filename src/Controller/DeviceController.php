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
    private $repo;
    private $security;

    public function __construct(EntityManagerInterface $manager, DeviceRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Rechercher un dispositif
     * 
     * @Route("/admin/devices", name="admin_devices")
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
     * Créer un dispositif
     * 
     * @Route("/admin/device/new", name="admin_device_new", methods="GET|POST")
     *  @return Response
     */
    public function createDevice(Device $device = null, Request $request): Response
    {
        $device = new Device();

        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->security->getUser();

            $device->setCreatedAt(new \DateTime())
                ->setCreatedBy($user)
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user);

            $this->manager->persist($device);
            $this->manager->flush();

            $this->addFlash("success", "Le dispositif a été créé.");

            return $this->redirectToRoute("admin_devices");
        }
        return $this->render("app/admin/device.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Editer la fiche du dispositif
     * 
     * @Route("/admin/device/{id}", name="admin_device_edit", methods="GET|POST")
     *  @return Response
     */
    public function editDevice(Device $device, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $form = $this->createForm(DeviceType::class, $device);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $device->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
            return $this->redirectToRoute("admin_devices");
        }
        return $this->render("app/admin/device.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }
}
