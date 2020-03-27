<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\Note;
use App\Entity\SupportGroup;
use App\Form\Model\NoteSearch;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NoteRepositoryTest extends WebTestCase
{
    use FixturesTrait;


    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var NoteRepository */
    protected $repo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var User */
    protected $user;

    /** @var NoteSearch */
    protected $noteSearch;


    protected function setUp()
    {
        $this->loadFixtureFiles([
            dirname(__DIR__) . "/Datafixtures/NoteFixturesTest.yaml",
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var NoteRepository */
        $this->repo = $this->entityManager->getRepository(Note::class);

        /** @var SupportGroupRepository */
        $repoSupportGroup = $this->entityManager->getRepository(SupportGroup::class);

        /** @var UserRepository */
        $repoUser = $this->entityManager->getRepository(User::class);

        $this->supportGroup = $repoSupportGroup->findOneBy([
            "startDate" => new \DateTime("2019-04-03"),
            "endDate" => null
        ]);
        $this->user = $repoUser->findOneBy(["username" => "r.madelaine"]);

        $this->noteSearch = (new NoteSearch())
            ->setContent("Contenu de la note")
            ->setType(1)
            ->setStatus(1);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->repo->count([]));
    }

    public function testFindAllNotesQueryWithoutFilters()
    {
        $query = $this->repo->findAllNotesQuery($this->supportGroup->getId(), new NoteSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllNotesQueryWithFilters()
    {
        $query = $this->repo->findAllNotesQuery($this->supportGroup->getId(), $this->noteSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllNotesQueryWithFilterByTitle()
    {
        $query = $this->repo->findAllNotesQuery($this->supportGroup->getId(), $this->noteSearch->setContent("Note 666"));
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllNotesFromUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findAllNotesFromUser($this->user, 10)));
    }


    public function testCountAllNotesWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllNotes());
    }

    public function testCountAllNotesWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllNotes(["user" => $this->user]));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
