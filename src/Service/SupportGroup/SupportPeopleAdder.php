<?php

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\People\Person;
use App\Entity\Support\SupportGroup;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Service\Grammar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SupportPeopleAdder
{
    use SupportPersonCreator;

    private $manager;
    private $evaluationRepo;
    private $flashbag;

    public function __construct(
        EntityManagerInterface $manager,
        EvaluationGroupRepository $evaluationRepo,
        FlashBagInterface $flashbag
    ) {
        $this->manager = $manager;
        $this->evaluationRepo = $evaluationRepo;
        $this->flashbag = $flashbag;
    }

    /**
     * Ajoute les personnes au suivi.
     */
    public function addPeopleInSupport(SupportGroup $supportGroup): bool
    {
        $addPeople = false;

        foreach ($supportGroup->getPeopleGroup()->getRolePeople() as $rolePerson) {
            if (!$this->personIsInSupport($rolePerson->getPerson(), $supportGroup)) {
                $supportPerson = $this->createSupportPerson($supportGroup, $rolePerson);
                $this->manager->persist($supportPerson);

                $supportGroup->addSupportPerson($supportPerson);
                $evaluationGroup = $this->evaluationRepo->findLastEvaluationOfSupport($supportGroup);

                if ($evaluationGroup) {
                    $evaluationPerson = (new EvaluationPerson())
                        ->setEvaluationGroup($evaluationGroup)
                        ->setSupportPerson($supportPerson);

                    $this->manager->persist($evaluationPerson);
                }

                $this->flashbag->add('success', $rolePerson->getPerson()->getFullname().' est ajouté'.Grammar::gender($supportPerson->getPerson()->getGender()).' au suivi.');

                $addPeople = true;
            }
        }

        return $addPeople;
    }

    /**
     * Vérifie si la personne est déjà dans le suivi social.
     */
    protected function personIsInSupport(Person $person, SupportGroup $supportGroup): bool
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($person === $supportPerson->getPerson()) {
                return true;
            }
        }

        return false;
    }
}
