<?php

namespace App\Repository;

use App\Entity\Note;
use App\Entity\User;
use App\Form\Model\NoteSearch;
use App\Form\Model\SupportNoteSearch;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * Return all notes of group support.
     */
    public function findAllNotesQuery(NoteSearch $search, ?CurrentUserService $currentUser = null): Query
    {
        $query = $this->createQueryBuilder('n')->select('n')
            ->leftJoin('n.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('n.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')
            ->leftJoin('n.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}');

        if ($currentUser && !$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->where('sg.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($search->getServices() && count($search->getServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($search->getSubServices() && count($search->getSubServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getSubServices() as $subService) {
                $orX->add($expr->eq('sg.subService', $subService));
            }
            $query->andWhere($orX);
        }

        if ($search->getDevices() && count($search->getDevices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        if ($search->getReferents() && count($search->getReferents())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

        if ($search->getContent()) {
            $query->andWhere('n.title LIKE :content OR n.content LIKE :content')
                ->setParameter('content', '%'.$search->getContent().'%');
        }
        if ($search->getStatus()) {
            $query->andWhere('n.status = :status')
                ->setParameter('status', $search->getStatus());
        }
        if ($search->getType()) {
            $query->andWhere('n.type = :type')
                ->setParameter('type', $search->getType());
        }

        if ($search->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }

        if ($search->getStart()) {
            $query->andWhere('n.createdAt >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('n.createdAt <= :end')
                ->setParameter('end', $search->getEnd());
        }

        return  $query->orderBy('n.updatedAt', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Return all notes of group support.
     */
    public function findAllNotesFromSupportQuery(int $supportGroupId, SupportNoteSearch $search): Query
    {
        $query = $this->createQueryBuilder('n')
            ->leftJoin('n.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('n.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')

            ->andWhere('n.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getNoteId()) {
            $query->andWhere('n.id = :id')
                ->setParameter('id', $search->getNoteId());
        }
        if ($search->getContent()) {
            $query->andWhere('n.title LIKE :content OR n.content LIKE :content')
                ->setParameter('content', '%'.$search->getContent().'%');
        }
        if ($search->getStatus()) {
            $query->andWhere('n.status = :status')
                ->setParameter('status', $search->getStatus());
        }
        if ($search->getType()) {
            $query->andWhere('n.type = :type')
                ->setParameter('type', $search->getType());
        }
        $query = $query->orderBy('n.createdAt', 'DESC');

        return $query->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     *  Donne toutes les notes créées par l'utilisateur.
     *
     * @return mixed
     */
    public function findAllNotesFromUser(User $user, int $maxResults = 1000)
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

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Compte le nombre de notes.
     *
     * @return mixed
     */
    public function countNotes(array $criteria = null)
    {
        $query = $this->createQueryBuilder('n')->select('COUNT(n.id)');

        if ($criteria) {
            $query = $query->leftJoin('n.supportGroup', 'sg');

            foreach ($criteria as $key => $value) {
                if ('service' == $key) {
                    $query = $query->andWhere('sg.service = :service')
                    ->setParameter('service', $value);
                }
                if ('subService' == $key) {
                    $query = $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' == $key) {
                    $query = $query->andWhere('sg.device = :device')
                    ->setParameter('device', $value);
                }
                if ('startDate' == $key) {
                    $query = $query->andWhere('n.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' == $key) {
                    $query = $query->andWhere('n.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
                if ('createdBy' == $key) {
                    $query = $query->andWhere('n.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
                if ('status' == $key) {
                    $query = $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
