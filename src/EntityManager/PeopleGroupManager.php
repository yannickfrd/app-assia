<?php

namespace App\EntityManager;

use App\Entity\EvaluationGroup;
use App\Entity\PeopleGroup;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\SupportGroup;
use App\Repository\ReferentRepository;
use App\Repository\RolePersonRepository;
use App\Repository\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PeopleGroupManager
{
    private $session;
    private $manager;
    private $cache;

    public function __construct(EntityManagerInterface $manager, SessionInterface $session)
    {
        $this->session = $session;
        $this->manager = $manager;
        $this->cache = new FilesystemAdapter();
    }

    /**
     * Met à jour un groupe de personnes.
     */
    public function update(PeopleGroup $peopleGroup, array $supports): void
    {
        $this->checkValidHead($peopleGroup);

        $this->manager->flush();

        $this->discacheSupports($supports);

        $this->addFlash('success', 'Les modifications sont enregistrées.');
    }

    /**
     * Supprime le groupe de personnes.
     */
    public function delete(PeopleGroup $peopleGroup): void
    {
        $this->manager->remove($peopleGroup);
        $this->manager->flush();
    }

    /**
     * Ajoute une personne dans le groupe.
     */
    public function addPerson(PeopleGroup $peopleGroup, RolePerson $rolePerson, person $person, RolePersonRepository $repoRolePerson): void
    {
        // Si la personne est asssociée, ne fait rien, créé la liaison
        if ($this->personExists($peopleGroup, $person, $repoRolePerson)) {
            $this->addFlash('warning', $person->getFullname().' est déjà associé'.Grammar::gender($person->getGender()).' au groupe.');

            return;
        }

        $rolePerson
            ->setHead(false)
            ->setPeopleGroup($peopleGroup);

        $person->addRolesPerson($rolePerson);

        $this->manager->persist($rolePerson);

        $peopleGroup->setNbPeople($peopleGroup->getRolePeople()->count() + 1); // Compte le nombre de personnes dans le groupe et ajoute 1

        $this->checkValidHead($peopleGroup);

        $this->manager->flush();

        $this->addFlash('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');

        return;
    }

    /**
     *  Vérifie si la personne est déjà rattachée à ce groupe.
     */
    protected function personExists(PeopleGroup $peopleGroup, Person $person, RolePersonRepository $repoRolePerson): ?RolePerson
    {
        return $repoRolePerson->findOneBy([
            'person' => $person->getId(),
            'peopleGroup' => $peopleGroup->getId(),
        ]);
    }

    /**
     * Retire une personne d'un groupe.
     */
    public function removePerson(RolePerson $rolePerson): array
    {
        $person = $rolePerson->getPerson();
        $peopleGroup = $rolePerson->getPeopleGroup();
        $nbPeople = $peopleGroup->getRolePeople()->count(); // // Compte le nombre de personnes dans le groupe

        // Vérifie si la personne est le demandeur principal
        if ($rolePerson->getHead()) {
            return [
                'code' => 200,
                'action' => 'error',
                'alert' => 'danger',
                'msg' => 'Le demandeur principal ne peut pas être retiré du groupe.',
                'data' => null,
            ];
        }

        $peopleGroup->removeRolePerson($rolePerson);
        $peopleGroup->setNbPeople($nbPeople - 1);

        $this->manager->flush();

        return [
            'code' => 200,
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $person->getFullname().' est retiré'.Grammar::gender($person->getGender()).' du groupe.',
            'data' => $nbPeople - 1,
        ];
    }

    /**
     * Vérifie la validité du demandeur principal.
     */
    protected function checkValidHead(PeopleGroup $peopleGroup): void
    {
        $nbHeads = 0;
        $maxAge = 0;
        $minorHead = false;

        foreach ($peopleGroup->getRolePeople() as $rolePerson) {
            $age = $rolePerson->getPerson()->getAge();
            if ($age > $maxAge) {
                $maxAge = $age;
            }
            if (true === $rolePerson->getHead()) {
                ++$nbHeads;
                if ($age < 18) {
                    $minorHead = true;
                    $this->addFlash('warning', 'Le demandeur principal a été automatiquement modifié, car il ne peut pas être mineur.');
                }
            }
        }

        if ($nbHeads != 1 || true === $minorHead) {
            foreach ($peopleGroup->getRolePeople() as $rolePerson) {
                $rolePerson->setHead(false);
                if ($rolePerson->getPerson()->getAge() === $maxAge) {
                    $rolePerson->setHead(true);
                }
            }
        }
    }

    public function getSupports(PeopleGroup $peopleGroup, SupportGroupRepository $repoSuppport)
    {
        return $this->cache->get(PeopleGroup::CACHE_GROUP_SUPPORTS_KEY.$peopleGroup->getId(), function (CacheItemInterface $item) use ($peopleGroup, $repoSuppport) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoSuppport->findSupportsOfPeopleGroup($peopleGroup);
        });
    }

    public function getReferents(PeopleGroup $peopleGroup, ReferentRepository $repoReferent)
    {
        return $this->cache->get(PeopleGroup::CACHE_GROUP_REFERENTS_KEY.$peopleGroup->getId(), function (CacheItemInterface $item) use ($peopleGroup, $repoReferent) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoReferent->findReferentsOfPeopleGroup($peopleGroup);
        });
    }

    /**
     * Supprime le suivis en cache.
     *
     * @param array|supportGroup[] $supports
     */
    protected function discacheSupports($supports): void
    {
        foreach ($supports as $supportGroup) {
            $this->cache->deleteItems([
                SupportGroup::CACHE_SUPPORT_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId(),
                EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(),
            ]);
        }
    }

    /**
     * Ajoute un message flash.
     */
    protected function addFlash(string $alert, string $msg)
    {
        $this->session->getFlashBag()->add($alert, $msg);
    }
}
