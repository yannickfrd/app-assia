<?php

namespace App\Service\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\InitEvalGroup;
use App\Entity\Evaluation\InitEvalPerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\ORM\EntityManagerInterface;

class EvaluationCreator
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Crée l'évaluation sociale du groupe.
     */
    public function create(SupportGroup $supportGroup): EvaluationGroup
    {
        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setDate(new \DateTime());

        if (!$supportGroup->getInitEvalGroup()) {
            $initEvalGroup = (new InitEvalGroup())->setSupportGroup($supportGroup);

            $this->manager->persist($initEvalGroup);

            $supportGroup->setInitEvalGroup($initEvalGroup);
        }

        $evaluationGroup->setInitEvalGroup($supportGroup->getInitEvalGroup());

        $this->manager->persist($evaluationGroup);

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $this->createEvaluationPerson($supportPerson, $evaluationGroup);
        }

        $this->manager->flush();

        return $evaluationGroup;
    }

    /**
     * Crée l'évaluation sociale d'une personne du groupe.
     */
    protected function createEvaluationPerson(SupportPerson $supportPerson, EvaluationGroup $evaluationGroup): EvaluationPerson
    {
        $evaluationPerson = (new EvaluationPerson())
            ->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson);

        if (!$supportPerson->getInitEvalPerson()) {
            $initEvalPerson = (new InitEvalPerson())->setSupportPerson($supportPerson);

            $this->manager->persist($initEvalPerson);

            $supportPerson->setInitEvalPerson($initEvalPerson);
        }

        $evaluationPerson->setInitEvalPerson($supportPerson->getInitEvalPerson());

        $this->manager->persist($evaluationPerson);

        return $evaluationPerson;
    }
}
