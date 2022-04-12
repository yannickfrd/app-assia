<?php

declare(strict_types=1);

namespace App\Service\SupportGroup;

use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\ORM\EntityManagerInterface;
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

        if (null === $supportPerson->getSupportGroup()->getDeletedAt()) {
            $supportPerson->setDeletedAt(null);

            return $this->translator->trans('support_person.restored_successfully', [
                '%support_name%' => $supportPerson->getPerson()->getFullname(),
            ], 'app');
        }

        $entitiesElement = $this->getElementsBeforeRestore($supportGroup);
        $this->resetDeletedAt($entitiesElement, $supportGroup->getDeletedAt());

        $supportGroup->setDeletedAt(null);

        SupportManager::deleteCacheItems($supportGroup);

        $this->em->flush();

        return $this->translator->trans('support.restored_successfully', [
            '%support_referent%' => $supportGroup->getHeader()->getFullname(),
        ], 'app');
    }

    private function resetDeletedAt(\Generator $collection, $supportDeletedAt) {
        foreach ($collection->current() as $entity) {
            if (null !== $entity->getDeletedAt() && $entity->getDeletedAt() == $supportDeletedAt) {
                $entity->setDeletedAt(null);
            }
        }
    }

    private function getElementsBeforeRestore(SupportGroup $supportGroup): \Generator
    {
        yield $supportGroup->getSupportPeople();
        yield $supportGroup->getNotes();
        yield $supportGroup->getRdvs();
        yield $supportGroup->getTasks();
        yield $supportGroup->getDocuments();
        yield $supportGroup->getHotelSupport();
        yield $supportGroup->getEvaluationsGroup();
        yield $supportGroup->getEvalInitGroup();
        yield $supportGroup->getOriginRequest();
        yield $supportGroup->getPeopleGroup();

        yield $supportGroup->getPlaceGroups();

        /** @var PlaceGroup $placeGroup */
        foreach ($supportGroup->getPlaceGroups() as $placeGroup) {
            yield $placeGroup->getPlacePeople();
        }
    }
}