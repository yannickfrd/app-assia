<?php

namespace App\Service\Indicators;

use App\Repository\UserRepository;
use App\Repository\DeviceRepository;
use App\Security\CurrentUserService;
use App\Form\Model\SupportsByUserSearch;
use App\Repository\SupportGroupRepository;

class SupportsByUserIndicators
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

    public function getSupportsbyDevice(SupportsByUserSearch $search)
    {
        $devices = $this->repoDevice->findDevicesForDashboard($this->currentUser, $search);
        $users = $this->repoUser->findAllUsersFromServices($this->currentUser, $search);
        $supports = $this->repoSupport->findSupportsForDashboard($search);

        $initDevicesUser = $this->getInitDevicesUser($devices);

        $devices = $initDevicesUser;
        $dataUsers = [];
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
            // Récupère le nombre de suivis théoriques de l'utilisateur
            foreach ($user->getUserDevices() as $userDevice) {
                $deviceId = $userDevice->getDevice()->getId();
                if (array_key_exists($deviceId, $devices)) {
                    $devicesUser[$deviceId]['nbTheoreticalSupports'] = $userDevice->getNbSupports();
                    $nbTheoreticalSupports += $userDevice->getNbSupports();
                    $devices[$deviceId]['nbTheoreticalSupports'] += $userDevice->getNbSupports();
                }
            }

            if ($nbUserSupports > 0) {
                $dataUsers[$user->getId()] = [
                    'user' => $user,
                    'nbSupports' => $nbUserSupports,
                    'nbTheoreticalSupports' => $nbTheoreticalSupports,
                    'sumCoeff' => $sumUserCoeff,
                    'devices' => $devicesUser,
                ];
            }
        }
        foreach ($devices as $deviceKey => $device) {
            if ($device['nbSupports'] == 0) {
                unset($devices[$deviceKey]);
            }
        }

        return [
            'nbSupports' => count($supports),
            'sumTheoreticalSupports' => $sumTheoreticalSupports,
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
