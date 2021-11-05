<?php

namespace App\Controller\Organization;

use App\Service\Pagination;
use App\Entity\Organization\Device;
use App\Security\CurrentUserService;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Organization\Device\DeviceType;
use App\Form\Model\Organization\DeviceSearch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Organization\DeviceRepository;
use App\Form\Organization\Device\DeviceSearchType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeviceController extends AbstractController
{
    private $deviceRepo;
    private $manager;

    public function __construct(DeviceRepository $deviceRepo, EntityManagerInterface $manager)
    {
        $this->deviceRepo = $deviceRepo;
        $this->manager = $manager;
    }

    /**
     * Affiche la liste des dispositifs.
     *
     * @Route("/admin/devices", name="admin_devices", methods="GET")
     */
    public function listDevice(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $form = $this->createForm(DeviceSearchType::class, $search = new DeviceSearch())
            ->handleRequest($request);

        return $this->render('app/organization/device/listDevices.html.twig', [
            'deviceSearch' => $search,
            'form' => $form->createView(),
            'devices' => $pagination->paginate($this->deviceRepo->findDevicesQuery($currentUser, $search), $request),
        ]);
    }

    /**
     * Nouveau dispositif.
     *
     * @Route("/admin/device/new", name="admin_device_new", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function newDevice(Request $request): Response
    {
        $form = $this->createForm(DeviceType::class, $device = new Device())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($device);
            $this->manager->flush();

            $this->addFlash('success', 'Le dispositif est créé.');
        }

        return $this->render('app/organization/device/device.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un dispositif.
     *
     * @Route("/admin/device/{id}", name="admin_device_edit", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function editDevice(Device $device, Request $request): Response
    {
        $form = $this->createForm(DeviceType::class, $device)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        return $this->render('app/organization/device/device.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Désactive ou réactive le dispositif.
     *
     * @Route("/admin/device/{id}/disable", name="admin_device_disable", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function disableDevice(Device $device): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($device->getDisabledAt()) {
            $device->setDisabledAt(null);
            $this->addFlash('success', 'Le dispositif est ré-activé.');
        } else {
            $device->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le dispositif est désactivé.');
        }

        $this->manager->flush();

        return $this->redirectToRoute('admin_device_edit', ['id' => $device->getId()]);
    }
}
