<?php

namespace App\Tests\Repository;

use App\Entity\Note;
use App\Entity\User;
use App\Entity\SupportGroup;
use App\Form\Model\NoteSearch;
use App\Form\Model\SupportNoteSearch;
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

    /** @var SupportNoteSearch */
    protected $search;

    protected function setUp()
    {
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/NoteFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repo = $this->entityManager->getRepository(Note::class);

        $this->supportGroup = $dataFixtures['supportGroup'];
        $this->user = $dataFixtures['userSuperAdmin'];
        $this->search = (new SupportNoteSearch())
            ->setContent('Contenu de la note')
            ->setType(1)
            ->setStatus(1);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->repo->count([]));
    }

    public function testFindAllNotesQueryWithoutFilters()
    {
        $query = $this->repo->findAllNotesQuery(new NoteSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllNotesFromSupportQueryWithoutFilters()
    {
        $query = $this->repo->findAllNotesFromSupportQuery($this->supportGroup->getId(), new SupportNoteSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllNotesFromSupportQueryWithFilters()
    {
        $query = $this->repo->findAllNotesFromSupportQuery($this->supportGroup->getId(), $this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllNotesFromSupportQueryWithFilterByTitle()
    {
        $query = $this->repo->findAllNotesFromSupportQuery($this->supportGroup->getId(), $this->search->setContent('Note 666'));
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllNotesFromUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findAllNotesFromUser($this->user)));
    }

    public function testCountAllNotesWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllNotes());
    }

    public function testCountAllNotesWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllNotes(['user' => $this->user]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->search = null;
    }
}
