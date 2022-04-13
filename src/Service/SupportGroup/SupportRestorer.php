<?php

declare(strict_types=1);

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Generator;
use Symfony\Contracts\Translation\TranslatorInterface;

class SupportRestorer
{
    private TranslatorInterface $translator;
    private EntityManagerInterface $em;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $em) {
        $this->translator = $translator;
        $this->em = $em;
    }

    public function restore(SupportPerson $supportPerson): string
    {
        $supportGroup = $supportPerson->getSupportGroup();

        $this->em->flush();

        if (null === $supportPerson->getSupportGroup()->getDeletedAt()) {
            $supportPerson->setDeletedAt(null);

            return $this->translator->trans('support_person.restored_successfully', [
                '%support_name%' => $supportPerson->getPerson()->getFullname(),
            ], 'app');
        }

        $this->resetDeletedAt($supportGroup);

        $this->em->flush();

        SupportManager::deleteCacheItems($supportGroup);

        return $this->translator->trans('support.restored_successfully', [
            '%support_referent%' => $supportGroup->getHeader()->getFullname(),
        ], 'app');
    }

    private function resetDeletedAt($supportGroup)
    {
        $entitiesElements = $this->getElementsBeforeRestore($supportGroup);
        foreach ($entitiesElements as $entities) {
            foreach ($entities as $entity) {
                dump($entity);
                if (null !== $entity && $entity->getDeletedAt() == $supportGroup->getDeletedAt()) {
                    $entity->setDeletedAt(null);
                }
            }
        }

        $supportGroup->setDeletedAt(null);
    }

    private function getElementsBeforeRestore(SupportGroup $supportGroup): Generator
    {
        yield from $this->getElementsPlaceGroups($supportGroup->getPlaceGroups());
        yield from $this->getElementsEvaluationGroup($supportGroup->getEvaluationsGroup());

        yield $supportGroup->getPlaceGroups();
        yield $supportGroup->getEvaluationsGroup();

        yield $supportGroup->getSupportPeople();
        yield $supportGroup->getNotes();
        yield $supportGroup->getRdvs();
        yield $supportGroup->getTasks();
        yield $supportGroup->getDocuments();
        yield $supportGroup->getPeopleGroup();

        yield [
            $supportGroup->getHotelSupport(),
            $supportGroup->getEvalInitGroup(),
            $supportGroup->getOriginRequest(),
            $supportGroup->getAvdl()
        ];
    }

    private function getElementsPlaceGroups(?Collection $getPlaceGroups): Generator
    {
        /** @var PlaceGroup $placeGroup */
        foreach ($getPlaceGroups as $placeGroup) {
            yield $placeGroup->getPlacePeople();
        }
    }

    private function getElementsEvaluationGroup(?Collection $evaluations): Generator
    {
        /** @var EvaluationGroup $evaluation */
        foreach ($evaluations as $evaluation) {
            yield from $this->getElementsEvaluationPeople($evaluation->getEvaluationPeople());
            yield $evaluation->getEvaluationPeople();

            yield [
                $evaluation->getEvalSocialGroup(),
                $evaluation->getEvalFamilyGroup(),
                $evaluation->getEvalHousingGroup(),
                $evaluation->getEvalBudgetGroup(),
                $evaluation->getEvalHotelLifeGroup(),
            ];
        }
    }

    private function getElementsEvaluationPeople(?Collection $evaluationPeople): Generator
    {
        /** @var EvaluationPerson $evaluationPerson */
        foreach ($evaluationPeople as $evaluationPerson) {
            yield [
                $evaluationPerson->getEvalAdmPerson(),
                $evaluationPerson->getEvalBudgetPerson(),
                $evaluationPerson->getEvalFamilyPerson(),
                $evaluationPerson->getEvalProfPerson(),
                $evaluationPerson->getEvalSocialPerson(),
                $evaluationPerson->getEvalJusticePerson(),
            ];
        }
    }
}