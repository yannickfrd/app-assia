<?php

namespace App\Service\Import;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use App\Repository\Support\HotelSupportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class ImportPAFDatas extends ImportDatas
{
    public const DEFAULT_PAYMENT_TYPE = 1;

    public const PAYMENT_TYPE = [
        'Virement automatique' => 1,
        'Virement mensuel' => 1,
        'Chèque' => 3,
        'Espèce' => 4,
    ];

    public const SOCIAL_WORKER = [
    ];

    protected $em;

    protected $fields;
    protected $field;

    protected $items = [];
    protected $hotelSupportRepo;
    protected $hotelSupports;

    protected $service;

    public function __construct(
        EntityManagerInterface $em,
        HotelSupportRepository $hotelSupportRepo
        ) {
        $this->em = $em;
        $this->hotelSupportRepo = $hotelSupportRepo;
        $this->hotelSupports = $hotelSupportRepo->findAll();
    }

    /**
     * Importe les données.
     *
     * @param Collection<Service> $services
     */
    public function importInDatabase(string $fileName, ArrayCollection $services): int
    {
        $this->fields = $this->getDatas($fileName);
        $this->users = $this->getUsers($services);

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

        $this->em->flush();

        return $this->items;
    }

    protected function createPAF(SupportGroup $supportGroup, array $paf)
    {
        $userReferent = $this->getUserReferent($paf['TS']);

        $comment = ($paf['Banque'] ? $paf['Banque']."\n" : null).
            (!$userReferent && $paf['TS'] ? 'TS : '.$paf['TS']."\n" : null).
            ($paf['Commentaire'] ?? null);

        $payment = (new Payment())
            ->setMonthContrib($paf['Date PAF'] ? new \Datetime($paf['Date PAF']) : null)
            ->setResourcesAmt((float) $paf['Montant ressources'])
            ->setToPayAmt((float) $paf['Montant à payer'])
            ->setPaidAmt((float) $paf['Montant réglé'])
            ->setType(Payment::CONTRIBUTION)
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

        $this->em->persist($payment);

        return $payment;
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

    /**
     * @param Collection<Service> $services
     */
    protected function getUsers(ArrayCollection $services): array
    {
        $users = [];

        foreach ($services as $service) {
            foreach ($service->getUsers() as $user) {
                foreach (self::SOCIAL_WORKER as $name) {
                    if (strstr($name, $user->getLastname())) {
                        $users[$name] = $user;
                    }
                }
            }
        }

        return $users;
    }
}
