<?php

namespace App\Repository\Support;

use App\Entity\Organization\User;
use App\Entity\Support\Note;
use App\Form\Model\Support\NoteSearch;
use App\Form\Model\Support\SupportNoteSearch;
use App\Repository\Traits\QueryTrait;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteRepository extends ServiceEntityRepository
{
    use QueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * Return all notes of group support.
     */
    public function findNotesQuery(NoteSearch $search, ?CurrentUserService $currentUser = null): Query
    {
        $qb = $this->createQueryBuilder('n')->select('n')
            ->leftJoin('n.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('n.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')
            ->join('n.supportGroup', 'sg')->addSelect('sg')
            ->join('sg.supportPeople', 'sp')->addSelect('sp')
            ->join('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->join('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->join('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}');

        if ($currentUser && !$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->where('sg.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($search->getId()) {
            $qb->andWhere('n.id = :id')
                ->setParameter('id', $search->getId());
        }

        $qb = $this->addOrganizationFilters($qb, $search);

        if ($search->getContent()) {
            $qb->andWhere('n.title LIKE :content OR n.content LIKE :content')
                ->setParameter('content', '%'.$search->getContent().'%');
        }
        if ($search->getStatus()) {
            $qb->andWhere('n.status = :status')
                ->setParameter('status', $search->getStatus());
        }
        if ($search->getType()) {
            $qb->andWhere('n.type = :type')
                ->setParameter('type', $search->getType());
        }

        if ($search->getFullname()) {
            $qb->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }

        if ($search->getStart()) {
            $qb->andWhere('n.createdAt >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $qb->andWhere('n.createdAt <= :end')
                ->setParameter('end', $search->getEnd());
        }

        return $qb
            ->orderBy('n.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * @return Note[]|null
     */
    public function findNotesOfSupport(int $supportGroupId, SupportNoteSearch $search): array
    {
        return $this->findNotesOfSupportQuery($supportGroupId, $search)
            ->getResult();
    }

    /**
     * Return all notes of group support.
     */
    public function findNotesOfSupportQuery(int $supportGroupId, SupportNoteSearch $search): Query
    {
        $qb = $this->createQueryBuilder('n')
            ->leftJoin('n.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('n.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')

            ->andWhere('n.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getNoteId()) {
            $qb->andWhere('n.id = :id')
                ->setParameter('id', $search->getNoteId());
        }
        if ($search->getContent()) {
            $qb->andWhere('n.title LIKE :content OR n.content LIKE :content')
                ->setParameter('content', '%'.$search->getContent().'%');
        }
        if ($search->getStatus()) {
            $qb->andWhere('n.status = :status')
                ->setParameter('status', $search->getStatus());
        }
        if ($search->getType()) {
            $qb->andWhere('n.type = :type')
                ->setParameter('type', $search->getType());
        }
        return $qb
            ->orderBy('n.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     *  Donne toutes les notes créées par l'utilisateur.
     *
     * @return Note[]|null
     */
    public function findNotesOfUser(User $user, int $maxResults = 1000): ?array
    {
        return $this->createQueryBuilder('n')
            ->addSelect('PARTIAL n.{id, title, status, createdAt, updatedAt}')

            ->leftJoin('n.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('n.createdBy = :user')
            ->setParameter('user', $user)
            ->andWhere('sp.head = TRUE')

            ->orderBy('n.updatedAt', 'DESC')

            ->setMaxResults($maxResults)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne une note.
     */
    public function findNote(int $id): ?Note
    {
        return $this->createQueryBuilder('n')->select('n')
            ->leftJoin('n.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name, logoPath}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->where('n.id = :id')
            ->setParameter('id', $id)

            ->orderBy('sp.head', 'DESC')

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getSingleResult();
    }

    /**
     * Compte le nombre de notes.
     */
    public function countNotes(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('n')->select('COUNT(n.id)');

        if ($criteria) {
            $dateFilter = $criteria['filterDateBy'] ?? 'createdAt';
            $qb->leftJoin('n.supportGroup', 'sg');

            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $qb = $this->addOrWhere($qb, 'sg.service', $value);
                }
                if ('subService' === $key) {
                    $qb = $this->addOrWhere($qb, 'sg.subService', $value);
                }
                if ('device' === $key) {
                    $qb = $this->addOrWhere($qb, 'sg.device', $value);
                }
                if ('status' === $key) {
                    $qb = $this->addOrWhere($qb, 'sg.status', $value);
                }
                if ('startDate' === $key) {
                    $qb->andWhere("n.$dateFilter >= :startDate")
                        ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere("n.$dateFilter <= :endDate")
                        ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $qb->andWhere('n.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }
}
