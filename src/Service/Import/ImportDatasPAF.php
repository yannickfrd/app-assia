<?php

namespace App\Service\Import;

use App\Entity\Contribution;
use App\Entity\SupportGroup;
use App\Repository\HotelSupportRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportDatasPAF
{
    use ImportTrait;

    public const DEFAULT_CONTRIBUTION_TYPE = 1;

    public const PAYMENT_TYPE = [
        'Virement automatique' => 1,
        'Virement mensuel' => 1,
        'Chèque' => 3,
        'Espèce' => 4,
    ];

    protected $manager;

    protected $items = [];
    protected $repoHotelSupport;
    protected $hotelSupports;
    protected $field;

    public function __construct(EntityManagerInterface $manager, HotelSupportRepository $repoHotelSupport)
    {
        $this->manager = $manager;
        $this->repoHotelSupport = $repoHotelSupport;
        $this->hotelSupports = $repoHotelSupport->findAll();
    }

    public function importInDatabase(string $fileName): array
    {
        $this->fields = $this->getDatas($fileName);

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

        foreach ($this->items as $key => $item) {
            $hotelSupport = $this->repoHotelSupport->findOneBy(['amhId' => $key]);
            if ($hotelSupport) {
                $this->items[$key]['groupPeople'] = $hotelSupport;
                foreach ($item['pafs'] as $paf) {
                    $this->createPAF($hotelSupport->getSupportGroup(), $paf);
                }
            }
        }

        // dd($this->items);
        $this->manager->flush();

        return $this->items;
    }

    protected function createPAF(SupportGroup $supportGroup, array $paf)
    {
        if (!$paf['Date PAF'] || !$paf['Montant à payer']) {
            return null;
        }

        $comment = ($paf['Banque'] ? $paf['Banque']."\n" : null).($paf['TS'] ? 'TS : '.$paf['TS']."\n" : null).($paf['Commentaire'] ?? null);

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
            ->setCreatedAt(new \Datetime($paf['Date création']))
            // ->setUpdatedAt(new \Datetime($paf['Date mise à jour']))
            ->setSupportGroup($supportGroup);

        $this->manager->persist($contribution);

        return $contribution;
    }
}
