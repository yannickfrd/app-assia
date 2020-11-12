<?php

namespace App\Service\Import;

use App\Entity\Rdv;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Repository\HotelSupportRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportDatasRdv extends ImportDatas
{
    public const STATUS = [
        'Présent' => 1,
        'Absent' => 2,
        'Annulé' => 3,
        'Non renseigné' => 99,
    ];

    public const SOCIAL_WORKER = [
        'Marie-Laure PEBORDE',
        'Camille RAVEZ',
        'Typhaine PECHE',
        'Cécile BAZIN',
        'Nathalie POULIQUEN',
        'Marina DJORDJEVIC',
        'Melody ROMET',
        'Gaëlle PRINCET',
        'Marion FRANCOIS',
        'Margot COURAUDON',
        'Marilyse TOURNIER',
        'Rozenn DOUELE ZAHAR',
        'Laurine VIALLE',
        'Ophélie QUENEL',
        'Camille GALAN',
        'Christine VESTUR',
        'Julie MARTIN',
    ];

    protected $manager;

    protected $fields;
    protected $field;

    protected $items = [];
    protected $repoHotelSupport;
    protected $hotelSupports;

    protected $service;

    public function __construct(
        EntityManagerInterface $manager,
        HotelSupportRepository $repoHotelSupport)
    {
        $this->manager = $manager;
        $this->repoHotelSupport = $repoHotelSupport;
        $this->hotelSupports = $repoHotelSupport->findAll();
    }

    public function importInDatabase(string $fileName, Service $service): int
    {
        $this->fields = $this->getDatas($fileName);
        $this->service = $service;

        $this->users = $this->getUsers();

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $this->items[$this->field['ID_ménage']]['rdvs'][] = [
                    'Nom' => $this->field['Nom ménage'].' '.$this->field['Prénom'],
                    'ID_RDV' => $this->field['ID_RDV'],
                    'Date RDV' => $this->field['Date RDV'],
                    'Heure RDV' => $this->field['Heure RDV'],
                    'TS' => $this->field['Travailleur social'],
                    'Notes' => $this->field['Notes'],
                    'Etat RDV' => $this->field['Etat RDV'],
                    'Date saisie' => $this->field['Date saisie'],
                ];
            }
            ++$i;
        }

        $nbRdvs = 0;
        foreach ($this->items as $key => $item) {
            $hotelSupport = $this->repoHotelSupport->findOneBy(['accessId' => $key]);
            if ($hotelSupport) {
                $this->items[$key]['groupPeople'] = $hotelSupport;
                foreach ($item['rdvs'] as $rdv) {
                    $this->createRdv($hotelSupport->getSupportGroup(), $rdv);
                    ++$nbRdvs;
                }
            }
        }

        // dd($this->items);
        $this->manager->flush();

        return $nbRdvs;
    }

    protected function createRdv(SupportGroup $supportGroup, array $rdv)
    {
        if (!$rdv['Date RDV']) {
            return null;
        }

        $userReferent = $this->getUserReferent($rdv['TS']);

        $start = new \Datetime($rdv['Date RDV'].' '.($rdv['Heure RDV'] ?? '00:00'));
        $end = (clone $start)->modify('+1 hour');
        $createdAt = new \Datetime($rdv['Date saisie']);
        $content = ($rdv['Notes'] ? $rdv['Notes']."\n" : '').
            (!$userReferent ? 'TS : '.$rdv['TS'] : '').
            ('Point téléphonique' === $rdv['Etat RDV'] ? "\n".$rdv['Etat RDV'] : null);

        $rdv = (new Rdv())
            ->setTitle('RDV '.$rdv['Nom'])
            ->setStart($start)
            ->setEnd($end)
            ->setContent($content)
            ->setStatus($this->findInArray($rdv['Etat RDV'], self::STATUS) ?? null)
            ->setCreatedBy($userReferent ?? $this->user)
            ->setCreatedAt($createdAt)
            ->setUpdatedBy($userReferent ?? $this->user)
            ->setUpdatedAt($createdAt)
            ->setSupportGroup($supportGroup);

        $this->manager->persist($rdv);

        return $rdv;
    }

    protected function getUserReferent(string $ts): ?User
    {
        foreach ($this->users as $key => $user) {
            if ($key === $ts) {
                return $user;
            }
        }

        return null;
    }

    protected function getUsers(): array
    {
        $users = [];

        foreach ($this->service->getUsers() as $user) {
            foreach (self::SOCIAL_WORKER as $name) {
                if (strstr($name, $user->getLastname())) {
                    $users[$name] = $user;
                }
            }
        }

        return $users;
    }
}
