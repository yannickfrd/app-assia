<?php

namespace App\Repository\People;

use App\Entity\People\PeopleGroup;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RolePerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method RolePerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method RolePerson[]    findAll()
 * @method RolePerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolePersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RolePerson::class);
    }

    /**
     * @return RolePerson[]|null
     */
    public function findPeopleInGroup(PeopleGroup $peopleGroup): array
    {
        return $this->createQueryBuilder('r')->select('r')
            ->leftJoin('r.person', 'p')->addSelect('p')

            ->where('r.peopleGroup = :peopleGroup')
            ->setParameter('peopleGroup', $peopleGroup)

            ->getQuery()
            ->getResult();
    }

    /**
     * @return RolePerson[]|null
     */
    public function findPeopleNotInSupport(SupportGroup $supportGroup): array
    {
        $rolePeople = [];

        foreach ($this->findPeopleInGroup($supportGroup->getPeopleGroup()) as $rolePerson) {
            if (!$this->personIsInSupport($rolePerson, $supportGroup)) {
                $rolePeople[] = $rolePerson;
            }
        }

        return $rolePeople;
    }

    /**
     * Vérifie si la personne est déjà dans le suivi social.
     */
    protected function personIsInSupport(RolePerson $rolePerson, SupportGroup $supportGroup): bool
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($rolePerson->getPerson() === $supportPerson->getPerson()) {
                return true;
            }
        }

        return false;
    }
}
