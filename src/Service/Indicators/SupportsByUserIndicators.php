<?php

namespace App\Service\Indicators;

use App\Entity\Organization\Device;
use App\Entity\Organization\User;
use App\Form\Model\Support\SupportsByUserSearch;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\UserRepository;
use App\Repository\Support\SupportGroupRepository;
use Symfony\Component\Security\Core\Security;

class SupportsByUserIndicators
{
    /** @var User */
    protected $user;
    protected $deviceRepo;
    protected $userRepo;
    protected $supportRepo;

    public function __construct(
        Security $security,
        DeviceRepository $deviceRepo,
        UserRepository $userRepo,
        SupportGroupRepository $supportRepo
    ) {
        $this->user = $security->getUser();
        $this->deviceRepo = $deviceRepo;
        $this->userRepo = $userRepo;
        $this->supportRepo = $supportRepo;
    }

    public function getSupportsbyDevice(SupportsByUserSearch $search)
    {
        $devices = $this->deviceRepo->findDevicesForDashboard($this->user, $search);
        $users = $this->userRepo->findUsersOfServices($this->user, $search);
        $supports = $this->supportRepo->findSupportsForDashboard($search);

        $initDevicesUser = $this->getInitDevicesUser($devices);

        $devices = $initDevicesUser;
        $datasUsers = [];
        $sumTheoreticalSupports = 0;
        $sumCoeffSupports = 0;

        foreach ($users as $user) {
            $nbUserSupports = 0;
            $nbTheoreticalSupports = 0;
            $sumUserCoeff = 0;
            $devicesUser = $initDevicesUser;
            foreach ($supports as $support) {
                if ($support->getReferent() == $user) {
                    ++$nbUserSupports;
                    $sumUserCoeff += $support->getCoefficient();
                    $deviceId = $support->getDevice() ? $support->getDevice()->getId() : 'NR';
                    if (array_key_exists($deviceId, $devicesUser)) {
                        ++$devicesUser[$deviceId]['nbSupports'];
                        $devicesUser[$deviceId]['sumCoeff'] += $support->getCoefficient();
                    }
                    if (array_key_exists($deviceId, $devices)) {
                        ++$devices[$deviceId]['nbSupports'];
                        $devices[$deviceId]['sumCoeff'] += $support->getCoefficient();
                    }
                    $sumCoeffSupports += $support->getCoefficient();
                }
            }
            // R??cup??re le nombre de suivis th??oriques de l'utilisateur
            foreach ($user->getUserDevices() as $userDevice) {
                $deviceId = $userDevice->getDevice()->getId();
                if (array_key_exists($deviceId, $devices)) {
                    $devicesUser[$deviceId]['nbTheoreticalSupports'] = $userDevice->getNbSupports();
                    $nbTheoreticalSupports += $userDevice->getNbSupports();
                    $devices[$deviceId]['nbTheoreticalSupports'] += $userDevice->getNbSupports();
                }
            }

            if ($nbUserSupports > 0) {
                $datasUsers[$user->getId()] = [
                    'user' => $user,
                    'nbSupports' => $nbUserSupports,
                    'nbTheoreticalSupports' => $nbTheoreticalSupports,
                    'sumCoeff' => $sumUserCoeff,
                    'devices' => $devicesUser,
                ];
            }
        }
        foreach ($devices as $deviceKey => $device) {
            if (0 === $device['nbSupports']) {
                unset($devices[$deviceKey]);
            }
        }

        return [
            'nbSupports' => count($supports),
            'sumTheoreticalSupports' => $sumTheoreticalSupports,
            'sumCoeffSupports' => $sumCoeffSupports,
            'devices' => $devices,
            'datasUsers' => $datasUsers,
        ];
    }

    /**
     * @param Device[] $devices
     */
    protected function getInitDevicesUser(array $devices): array
    {
        $initDevicesUser = [];

        foreach ($devices as $device) {
            $initDevicesUser[$device->getId()] = [
                'name' => $device->getName(),
                'nbSupports' => 0,
                'nbTheoreticalSupports' => 0,
                'sumCoeff' => 0,
            ];
        }

        $initDevicesUser['NR'] = [
                'name' => 'NR',
                'nbSupports' => 0,
                'nbTheoreticalSupports' => 0,
                'sumCoeff' => 0,
        ];

        return $initDevicesUser;
    }
}
