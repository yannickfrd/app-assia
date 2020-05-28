<?php

namespace App\Service;

use App\Repository\DeviceRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\UserRepository;
use App\Security\CurrentUserService;

class Indicators
{
    protected $currentUser;
    protected $repoDevice;
    protected $repoUser;
    protected $repoSupport;

    public function __construct(
        CurrentUserService $currentUser,
        DeviceRepository $repoDevice,
        UserRepository $repoUser,
        SupportGroupRepository $repoSupport)
    {
        $this->currentUser = $currentUser;
        $this->repoDevice = $repoDevice;
        $this->repoUser = $repoUser;
        $this->repoSupport = $repoSupport;
    }

    public function getSupportsbyDevice()
    {
        $devices = $this->repoDevice->findDevicesForDashboard($this->currentUser);
        $users = $this->repoUser->findAllUsersFromServices($this->currentUser);
        $supports = $this->repoSupport->findSupportsForDashboard();

        $initDevicesUser = $this->getInitDevicesUser($devices);

        $devices = $initDevicesUser;
        $dataUsers = [];
        $sumCoeffSupports = 0;

        foreach ($users as $user) {
            $nbUserSupports = 0;
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
            if ($nbUserSupports > 0) {
                $dataUsers[$user->getId()] = [
                    'user' => $user,
                    'nbSupports' => $nbUserSupports,
                    'sumCoeff' => $sumUserCoeff,
                    'devices' => $devicesUser,
                ];
            }
        }

        return [
            'nbSupports' => count($supports),
            'sumCoeffSupports' => $sumCoeffSupports,
            'devices' => $devices,
            'dataUsers' => $dataUsers,
        ];
    }

    protected function getInitDevicesUser($devices)
    {
        $initDevicesUser = [];

        foreach ($devices as $device) {
            $initDevicesUser[$device->getId()] = [
                'name' => $device->getName(),
                'nbSupports' => 0,
                'sumCoeff' => 0,
            ];
        }

        $initDevicesUser['NR'] = [
                'name' => 'NR',
                'nbSupports' => 0,
                'sumCoeff' => 0,
        ];

        return $initDevicesUser;
    }
}
