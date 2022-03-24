<?php

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\PlacePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Service\Grammar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SupportPeopleAdder
{
    use SupportPersonCreator;

    private $em;
    private $flashbag;

    public function __construct(EntityManagerInterface $em, FlashBagInterface $flashbag)
    {
        $this->em = $em;
        $this->flashbag = $flashbag;
    }

    public function addPersonToSupport(SupportGroup $supportGroup, RolePerson $rolePerson): ?SupportPerson
    {
        $person = $rolePerson->getPerson();

        if ($this->personIsInSupport($person, $supportGroup)) {
            $this->flashbag->add('warning', $person->getFullname().' est déjà rattaché'.Grammar::gender($person->getGender()).' au suivi.');

            return null;
        }

        $supportPerson = $this->createSupportPerson($supportGroup, $rolePerson);

        $this->em->persist($supportPerson);

        $supportGroup->addSupportPerson($supportPerson);

        $this->createPlacePerson($supportGroup, $supportPerson);
        $this->createEvaluationPerson($supportGroup, $supportPerson);

        $this->em->flush();

        $this->flashbag->add('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au suivi en cours.');

        return $supportPerson;
    }

    /**
     * Si un hébergement est actuellement en cours, alors ajoute cette personne à celui-ci.
     */
    protected function createPlacePerson(SupportGroup $supportGroup, SupportPerson $supportPerson): ?PlacePerson
    {
        /** @var PlaceGroup $placeGroup */
        $placeGroup = $supportGroup->getPlaceGroups()->last();

        if (!$placeGroup || null === $placeGroup->getStartDate() || $placeGroup->getEndDate()) {
            return null;
        }

        $placePerson = (new PlacePerson())
                ->setStartDate($supportPerson->getStartDate())
                ->setPlaceGroup($placeGroup)
                ->setSupportPerson($supportPerson)
                ->setPerson($supportPerson->getPerson());

        $this->em->persist($placePerson);

        return $placePerson;
    }

    protected function createEvaluationPerson(SupportGroup $supportGroup, SupportPerson $supportPerson): ?EvaluationPerson
    {
        /** @var EvaluationGroupRepository $evaluationGroupRepo */
        $evaluationGroupRepo = $this->em->getRepository(EvaluationGroup::class);

        $evaluationGroup = $evaluationGroupRepo->findLastEvaluationOfSupport($supportGroup);

        if (null === $evaluationGroup) {
            return null;
        }

        $evaluationPerson = (new EvaluationPerson())
            ->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson);

        $this->em->persist($evaluationPerson);

        return $evaluationPerson;
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
