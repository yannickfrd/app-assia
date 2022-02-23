<?php

namespace App\Service\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\EvalInitGroup;
use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\ORM\EntityManagerInterface;

class EvaluationCreator
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Crée l'évaluation sociale du suivi.
     */
    public function create(SupportGroup $supportGroup): EvaluationGroup
    {
        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setDate(new \DateTime());

        if (!$supportGroup->getEvalInitGroup()) {
            $evalInitGroup = (new EvalInitGroup())->setSupportGroup($supportGroup);

            $this->em->persist($evalInitGroup);

            $supportGroup->setEvalInitGroup($evalInitGroup);
        }

        $evaluationGroup->setEvalInitGroup($supportGroup->getEvalInitGroup());

        $this->em->persist($evaluationGroup);

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $this->createEvaluationPerson($supportPerson, $evaluationGroup);
        }

        $this->em->flush();

        return $evaluationGroup;
    }

    /**
     * Crée l'évaluation sociale d'une personne du suivi.
     */
    public function createEvaluationPerson(SupportPerson $supportPerson, EvaluationGroup $evaluationGroup): EvaluationPerson
    {
        $evaluationPerson = (new EvaluationPerson())
            ->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson);

        if (!$supportPerson->getEvalInitPerson()) {
            $evalInitPerson = (new EvalInitPerson())->setSupportPerson($supportPerson);

            $this->em->persist($evalInitPerson);

            $supportPerson->setEvalInitPerson($evalInitPerson);
        }

        $evaluationPerson->setEvalInitPerson($supportPerson->getEvalInitPerson());

        $this->em->persist($evaluationPerson);

        return $evaluationPerson;
    }
}
