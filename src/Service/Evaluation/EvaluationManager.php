<?php

namespace App\Service\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Service\Grammar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class EvaluationManager extends EvaluationCreator
{
    private $em;
    private $flasgBag;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flashBag)
    {
        $this->em = $em;
        $this->flasgBag = $flashBag;

        parent::__construct($em);
    }

    public function addEvaluationPeople(EvaluationGroup $evaluationGroup): void
    {
        $supportGroup = $evaluationGroup->getSupportGroup();
        $supportPeople = $supportGroup->getSupportPeople();

        if ($evaluationGroup->getEvaluationPeople()->count() != $supportPeople->count()) {
            foreach ($supportPeople as $supportPerson) {
                if (false === $this->personIsInEvaluation($supportPerson, $evaluationGroup)) {
                    $person = $supportPerson->getPerson();
                    $this->createEvaluationPerson($supportPerson, $evaluationGroup);

                    $this->flasgBag->add('success', "{$person->getFullname()} a été ajouté".
                        Grammar::gender($person->getGender())." à l'évaluation sociale.");
                }
            }
        }

        $this->em->flush();
    }

    public static function personIsInEvaluation(SupportPerson $supportPerson, EvaluationGroup $evaluationGroup): bool
    {
        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            if ($supportPerson->getId() === $evaluationPerson->getSupportPerson()->getId()) {
                return true;
            }
        }

        return false;
    }

    public static function personIsInSupport(EvaluationPerson $evaluationPerson, SupportGroup $supportGroup): bool
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getId() === $evaluationPerson->getSupportPerson()->getId()) {
                return true;
            }
        }

        return false;
    }
}
