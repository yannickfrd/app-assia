<?php

namespace App\Service\Service;

use App\Entity\Admin\Setting;
use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceSetting;
use App\Repository\Organization\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;

class ServiceManager
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createService(): Service
    {
        return (new Service())->setSetting($this->hydrateServiceSetting(new ServiceSetting()));
    }

    public function getFullService(int $id): Service
    {
        /** @var ServiceRepository $serviceRepo */
        $serviceRepo = $this->em->getRepository(Service::class);

        $service = $serviceRepo->getFullService($id);

        if (!$service) { // If service don't exist, return new service
            return $this->createService();
        }

        // If the service does not contain any parameters, we get those of the application.
        return ($service->getSetting())
            ? $service
            : $service->setSetting($this->hydrateServiceSetting(new ServiceSetting()));
    }

    private function hydrateServiceSetting(ServiceSetting $serviceSetting): ServiceSetting
    {
        $defaultSetting = $this->em->getRepository(Setting::class)->findOneBy([]) ?? new Setting();

        $serviceSetting
            ->setWeeklyAlert($defaultSetting->getWeeklyAlert())
            ->setDailyAlert($defaultSetting->getDailyAlert())
            ->setSoftDeletionDelay($defaultSetting->getSoftDeletionDelay())
            ->setHardDeletionDelay($defaultSetting->getHardDeletionDelay());

        return $serviceSetting;
    }
}
