<?php

namespace App\Repository;

use App\Entity\Note;
use App\Entity\User;
use App\Form\Model\NoteSearch;
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
    public function findAllNotesQuery(int $supportGroupId, NoteSearch $noteSearch): Query
    {
        $query = $this->createQueryBuilder('n')
            ->andWhere('n.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($noteSearch->getContent()) {
            $query->andWhere('n.title LIKE :content OR n.content LIKE :content')
                ->setParameter('content', '%'.$noteSearch->getContent().'%');
        }
        if ($noteSearch->getStatus()) {
            $query->andWhere('n.status = :status')
                ->setParameter('status', $noteSearch->getStatus());
        }
        if ($noteSearch->getType()) {
            $query->andWhere('n.type = :type')
                ->setParameter('type', $noteSearch->getType());
        }
        $query = $query->orderBy('n.createdAt', 'DESC');

        return $query->getQuery();
    }

    /**
     *  Donne toutes les notes créées par l'utilisateur.
     *
     * @return mixed
     */
    public function findAllNotesFromUser(User $user, int $maxResults = 1000)
    {
        return $this->createQueryBuilder('n')
            ->addselect('PARTIAL n.{id, title, status, createdAt, updatedAt}')

            ->leftJoin('n.supportGroup', 'sg')->addselect('PARTIAL sg.{id}')
            ->leftJoin('sg.supportPeople', 'sp')->addselect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname}')

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
    public function countAllNotes(array $criteria = null)
    {
        $query = $this->createQueryBuilder('n')->select('COUNT(n.id)');

        if ($criteria) {
            // $query = $query->leftJoin("n.supportGroup", "sg")->addselect("PARTIAL sg.{id, referent, status, service, device}");

            foreach ($criteria as $key => $value) {
                if ('user' == $key) {
                    $query = $query->andWhere('n.createdBy = :user')
                        ->setParameter('user', $value);
                }
                if ('status' == $key) {
                    $query = $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
                if ('service' == $key) {
                    $query = $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('device' == $key) {
                    $query = $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
