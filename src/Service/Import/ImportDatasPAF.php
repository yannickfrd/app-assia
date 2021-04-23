<?php

namespace App\Service\Import;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\Support\Contribution;
use App\Entity\Support\SupportGroup;
use App\Repository\Support\HotelSupportRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportDatasPAF extends ImportDatas
{
    public const DEFAULT_CONTRIBUTION_TYPE = 1;

    public const PAYMENT_TYPE = [
        'Virement automatique' => 1,
        'Virement mensuel' => 1,
        'Chèque' => 3,
        'Espèce' => 4,
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
    protected $hotelSupportRepo;
    protected $hotelSupports;

    protected $service;

    public function __construct(
        EntityManagerInterface $manager,
        HotelSupportRepository $hotelSupportRepo
        ) {
        $this->manager = $manager;
        $this->hotelSupportRepo = $hotelSupportRepo;
        $this->hotelSupports = $hotelSupportRepo->findAll();
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
                $this->items[$this->field['ID_ménage']]['pafs'][] = [
                    'Nom' => $this->field['Nom ménage'].' '.$this->field['Prénom'],
                    'ID_PAF' => $this->field['ID_PAF'],
                    'Date PAF' => $this->field['Date PAF'],
                    'Montant à payer' => $this->field['Montant à payer'],
                    'Montant réglé' => $this->field['Montant réglé'],
                    'Date paiement' => $this->field['Date paiement'],
                    'Ecart montant' => $this->field['Ecart montant'],
                    'Type paiement' => $this->field['Type paiement'],
                    'Banque' => $this->field['Banque'],
                    'Montant ressources' => $this->field['Montant ressources'],
                    'TS' => $this->field['TS AMH'],
                    'Commentaire' => $this->field['Commentaire'],
                    'Date vérification' => $this->field['Date vérification'],
                    'Date création' => $this->field['Date création'],
                    'Date mise à jour' => $this->field['Date mise à jour'],
                ];
            }
            ++$i;
        }

        $nbPafs = 0;
        foreach ($this->items as $key => $item) {
            $hotelSupport = $this->hotelSupportRepo->findOneBy(['accessId' => $key]);
            if ($hotelSupport) {
                $this->items[$key]['peopleGroup'] = $hotelSupport;
                foreach ($item['pafs'] as $paf) {
                    $this->createPAF($hotelSupport->getSupportGroup(), $paf);
                    ++$nbPafs;
                }
            }
        }

        // dd($this->items);
        $this->manager->flush();

        return $nbPafs;
    }

    protected function createPAF(SupportGroup $supportGroup, array $paf)
    {
        $userReferent = $this->getUserReferent($paf['TS']);

        $comment = ($paf['Banque'] ? $paf['Banque']."\n" : null).
            (!$userReferent && $paf['TS'] ? 'TS : '.$paf['TS']."\n" : null).
            ($paf['Commentaire'] ?? null);

        $contribution = (new Contribution())
            ->setMonthContrib($paf['Date PAF'] ? new \Datetime($paf['Date PAF']) : null)
            ->setResourcesAmt((float) $paf['Montant ressources'])
            ->setToPayAmt((float) $paf['Montant à payer'])
            ->setPaidAmt((float) $paf['Montant réglé'])
            ->setType(Contribution::TYPE_CONTRIBUTION)
            ->setPaymentDate($paf['Date paiement'] ? new \Datetime($paf['Date paiement']) : null)
            ->setStillToPayAmt()
            ->setPaymentType($this->findInArray($paf['Type paiement'], self::PAYMENT_TYPE) ?? 99)
            ->setComment($comment)
            ->setCheckAt(new \Datetime($paf['Date vérification']))
            ->setCreatedBy($userReferent ?? $this->user)
            ->setCreatedAt(new \Datetime($paf['Date création']))
            ->setUpdatedBy($userReferent ?? $this->user)
            ->setUpdatedAt(new \Datetime($paf['Date mise à jour']))
            ->setSupportGroup($supportGroup);

        $this->manager->persist($contribution);

        return $contribution;
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
