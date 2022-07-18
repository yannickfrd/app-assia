<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization\Device;
use App\Form\Model\Organization\DeviceSearch;
use App\Form\Organization\Device\DeviceSearchType;
use App\Form\Organization\Device\DeviceType;
use App\Repository\Organization\DeviceRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class DeviceController extends AbstractController
{
    private $deviceRepo;
    private $em;

    public function __construct(DeviceRepository $deviceRepo, EntityManagerInterface $em)
    {
        $this->deviceRepo = $deviceRepo;
        $this->em = $em;
    }

    /**
     * @Route("/admin/devices", name="admin_devices", methods="GET")
     */
    public function index(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(DeviceSearchType::class, $search = new DeviceSearch())
            ->handleRequest($request);

        return $this->render('app/organization/device/device_index.html.twig', [
            'deviceSearch' => $search,
            'form' => $form->createView(),
            'devices' => $pagination->paginate($this->deviceRepo->findDevicesQuery($search, $this->getUser()), $request),
        ]);
    }

    /**
     * @Route("/admin/device/new", name="admin_device_new", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function new(Request $request): Response
    {
        $form = $this->createForm(DeviceType::class, $device = new Device())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($device);
            $this->em->flush();

            $this->addFlash('success', 'device.created_successfully');

            return $this->redirectToRoute('admin_device_edit', ['id' => $device->getId()]);
        }

        return $this->render('app/organization/device/device_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/device/{id}", name="admin_device_edit", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function edit(Device $device, Request $request): Response
    {
        $form = $this->createForm(DeviceType::class, $device)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'device.updated_successfully');
        }

        return $this->render('app/organization/device/device_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/device/{id}/disable", name="admin_device_disable", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function disable(Device $device): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($device->isDisabled()) {
            $device->setDisabledAt(null);
            $this->addFlash('success', 'device.actived_successfully');
        } else {
            $device->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'device.disabled_successfully');
        }

        $this->em->flush();

        return $this->redirectToRoute('admin_device_edit', ['id' => $device->getId()]);
    }
}
