<?php

namespace App\Service\Import;

use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateDatasAMH extends ImportDatas
{
    protected $manager;

    protected $items = [];
    protected $repoPerson;
    protected $field;

    protected $service;

    public function __construct(
        EntityManagerInterface $manager,
        PersonRepository $repoPerson)
    {
        $this->manager = $manager;
        $this->repoPerson = $repoPerson;
    }

    public function importInDatabase(string $fileName): int
    {
        $this->fields = $this->getDatas($fileName);

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $this->items[] = [
                    'lastname' => $this->field['Nom'],
                    'firstname' => $this->field['Prénom'],
                    'birthdate' => $this->field['Date naissance'],
                    'admin' => $this->field['Situation administrative'],
                    'idPerson' => $this->field['Id Personne'],
                ];
            }
            ++$i;
        }

        // dump($this->items);

        $nbUpdatedPeople = 0;
        foreach ($this->items as $key => $item) {
            $person = $this->repoPerson->findOneBy([
                'lastname' => $item['lastname'],
                'firstname' => $item['firstname'],
                'birthdate' => new \Datetime($item['birthdate']),
            ]);
            if ($person) {
                foreach ($person->getSupports() as $supportPerson) {
                    foreach ($supportPerson->getEvaluationsPerson() as $evaluationPerson) {
                        $evalAdmPerson = $evaluationPerson->getEvalAdmPerson();
                        if ($evalAdmPerson && $evalAdmPerson->getPaperType() === 1 && $evalAdmPerson->getPaper() != 1 && $evalAdmPerson->getNationality() != 1) {
                            $evalAdmPerson->setPaperType(null);
                            ++$nbUpdatedPeople;
                        }
                    }
                }
            }
        }

        // dd($nbUpdatedPeople);
        $this->manager->flush();

        return $nbUpdatedPeople;
    }
}
