<?php

namespace App\Service\Import;

use App\Entity\Rdv;
use App\Entity\SupportGroup;
use App\Repository\HotelSupportRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportDatasRdv
{
    use ImportTrait;

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
                $this->items[$this->field['ID_ménage']]['rdvs'][] = [
                    'Nom' => $this->field['Nom ménage'].' '.$this->field['Prénom'],
                    'ID_RDV' => $this->field['ID_RDV'],
                    'Date RDV' => $this->field['Date RDV'],
                    'Heure RDV' => $this->field['Heure RDV'],
                    'TS' => $this->field['Travailleur social'],
                    'Notes' => $this->field['Notes'],
                    'Etat RDV' => $this->field['Etat RDV'],
                ];
            }
            ++$i;
        }

        foreach ($this->items as $key => $item) {
            $hotelSupport = $this->repoHotelSupport->findOneBy(['amhId' => $key]);
            if ($hotelSupport) {
                $this->items[$key]['groupPeople'] = $hotelSupport;
                foreach ($item['rdvs'] as $rdv) {
                    $this->createRdv($hotelSupport->getSupportGroup(), $rdv);
                }
            }
        }

        // dd($this->items);
        $this->manager->flush();

        return $this->items;
    }

    protected function createRdv(SupportGroup $supportGroup, array $rdv)
    {
        if (!$rdv['Date RDV']) {
            return null;
        }

        $start = new \Datetime($rdv['Date RDV'].' '.($rdv['Heure RDV'] ?? '00:00'));
        $end = (clone $start)->modify('+1 hour');
        $content = ($rdv['Notes'] ? $rdv['Notes']."\n" : '').'TS : '.$rdv['TS']."\nStatut RDV : ".$rdv['Etat RDV'];

        $rdv = (new Rdv())
            ->setTitle('RDV '.$rdv['Nom'])
            ->setStart($start)
            ->setEnd($end)
            ->setContent($content)
            ->setSupportGroup($supportGroup);

        $this->manager->persist($rdv);

        return $rdv;
    }
}
