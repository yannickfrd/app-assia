<?php

namespace App\Service\Service;

use App\Entity\Admin\Setting;
use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceSetting;
use App\Repository\Organization\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionObject;

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
        return (new Service())->setSetting($this->hydrateServiceSetting());
    }

    public function getFullService(int $id): Service
    {
        /** @var ServiceRepository $serviceRepo */
        $serviceRepo = $this->em->getRepository(Service::class);

        $service = $serviceRepo->getFullService($id);

        if (!$service->getSetting()) {
            $service->setSetting($this->hydrateServiceSetting());
        }

        return $service;
    }

    private function hydrateServiceSetting(): ServiceSetting
    {
        $defaultSetting = $this->em->getRepository(Setting::class)->findOneBy([]) ?? new Setting();
        $serviceSetting = new ServiceSetting();

        $reflectionServiceSetting = new ReflectionObject($serviceSetting);

        foreach ((new ReflectionObject($defaultSetting))->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $getMethod = 'get'.ucfirst($propertyName);
            $setMethod = 'set'.ucfirst($propertyName);
            if ($reflectionServiceSetting->hasMethod($setMethod)) {
                $serviceSetting->$setMethod($defaultSetting->$getMethod());
            }
        }

        return $serviceSetting;
    }
}
