<?php

declare(strict_types=1);

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Service\Grammar;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SupportRestorer
{
    private $em;
    private $translator;
    private bool $supportGroupIsDeleted = false;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    public function restore(SupportPerson $supportPerson): string
    {
        $supportGroup = $supportPerson->getSupportGroup();
        $this->supportGroupIsDeleted = $supportGroup->isDeleted();

        $this->resetDeletedAt($supportGroup, $supportPerson);

        $this->em->flush();

        SupportManager::deleteCacheItems($supportGroup);

        if ($this->supportGroupIsDeleted) {
            return $this->translator->trans('support_group.restored_successfully', [
                'support_name' => $supportGroup->getHeader()->getFullname(),
            ], 'app');
        }

        return $this->translator->trans('support_person.restored_successfully', [
            'person_fullname' => $supportPerson->getPerson()->getFullname(),
            'e' => Grammar::gender($supportPerson->getPerson()->getGender()),
        ], 'app');
    }

    private function resetDeletedAt(SupportGroup $supportGroup, SupportPerson $supportPerson): void
    {
        foreach ($this->supportGroupGenerator($supportGroup) as $entities) {
            foreach ($entities as $entity) {
                if (null !== $entity && $entity->getDeletedAt() == $supportPerson->getDeletedAt()) {
                    $entity->setDeletedAt(null);
                }
            }
        }

        $supportDeletedAt = $supportPerson->getDeletedAt();

        foreach ($supportGroup->getSupportPeople() as $sp) {
            $person = $sp->getPerson();

            if ($supportGroup->isDeleted() || (false === $sp->isDeleted() && $person->isDeleted())
                || $sp->getId() === $supportPerson->getId()) {
                foreach ($person->getRolesPerson() as $rolePerson) {
                    if ($rolePerson->getDeletedAt() == $supportDeletedAt
                        || $rolePerson->getDeletedAt() == $person->getDeletedAt()) {
                        $rolePerson->setDeletedAt(null);
                    }
                }
                $person->setDeletedAt(null);
            }
            if ($supportGroup->isDeleted() && $sp->getDeletedAt() == $supportDeletedAt) {
                $sp->setDeletedAt(null);
            }
        }

        $supportPerson->setDeletedAt(null);
        $supportGroup->setDeletedAt(null);
        $supportGroup->getPeopleGroup()->setDeletedAt(null);
    }

    /**
     * Collect all the getters for the SupportGroup.
     */
    private function supportGroupGenerator(SupportGroup $supportGroup): \Generator
    {
        yield from $this->placeGroupsGenerator($supportGroup->getPlaceGroups());
        yield from $this->evaluationGroupGenerator($supportGroup->getEvaluationsGroup());

        if ($this->supportGroupIsDeleted) {
            yield $supportGroup->getPlaceGroups();
            yield $supportGroup->getEvaluationsGroup();

            yield $supportGroup->getPeopleGroup()->getReferents();
            yield $supportGroup->getNotes();
            yield $supportGroup->getRdvs();
            yield $supportGroup->getTasks();
            yield $supportGroup->getDocuments();
            yield $supportGroup->getPayments();

            yield [
                $supportGroup->getOriginRequest(),
                $supportGroup->getEvalInitGroup(),
                $supportGroup->getAvdl(),
                $supportGroup->getHotelSupport(),
            ];
        }
    }

    /**
     * Collect all the getters for the PlaceGroup.
     *
     * @param Collection<PlaceGroup> $placeGroups
     */
    private function placeGroupsGenerator(?Collection $placeGroups): \Generator
    {
        foreach ($placeGroups as $placeGroup) {
            yield $placeGroup->getPlacePeople();
        }
    }

    /**
     * Collect all the getters for the EvaluationGroup.
     *
     * @param Collection<EvaluationGroup> $evaluationGroups
     */
    private function evaluationGroupGenerator(?Collection $evaluationGroups): \Generator
    {
        foreach ($evaluationGroups as $evaluationGroup) {
            yield from $this->evaluationPeopleGenerator($evaluationGroup->getEvaluationPeople());
            yield $evaluationGroup->getEvaluationPeople();

            yield [
                $evaluationGroup->getEvalFamilyGroup(),
                $evaluationGroup->getEvalSocialGroup(),
                $evaluationGroup->getEvalBudgetGroup(),
                $evaluationGroup->getEvalHousingGroup(),
                $evaluationGroup->getEvalHotelLifeGroup(),
            ];
        }
    }

    /**
     * Collect all the getters for the EvaluationPeople.
     *
     * @param Collection<EvaluationPerson> $evaluationPeople
     */
    private function evaluationPeopleGenerator(Collection $evaluationPeople): \Generator
    {
        foreach ($evaluationPeople as $evaluationPerson) {
            yield [
                $evaluationPerson->getEvalInitPerson(),
                $evaluationPerson->getEvalAdmPerson(),
                $evaluationPerson->getEvalJusticePerson(),
                $evaluationPerson->getEvalFamilyPerson(),
                $evaluationPerson->getEvalSocialPerson(),
                $evaluationPerson->getEvalProfPerson(),
                $evaluationPerson->getEvalBudgetPerson(),
            ];
        }
    }
}
