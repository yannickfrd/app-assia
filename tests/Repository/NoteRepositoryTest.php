<?php

namespace App\Tests\Repository;

use App\Entity\Support\Note;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\NoteSearch;
use App\Form\Model\Support\SupportNoteSearch;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class NoteRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var NoteRepository */
    protected $noteRepo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var User */
    protected $user;

    /** @var SupportNoteSearch */
    protected $search;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/note_fixtures_test.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->noteRepo = $this->entityManager->getRepository(Note::class);

        $this->supportGroup = $fixtures['support_group1'];
        $this->user = $fixtures['john_user'];
        $this->search = (new SupportNoteSearch())
            ->setContent('Contenu de la note')
            ->setType(1)
            ->setStatus(1);
    }

    public function testCount(): void
    {
        $this->assertGreaterThanOrEqual(2, $this->noteRepo->count([]));
    }

    public function testFindAllNotesQueryWithoutFilters(): void
    {
        $qb = $this->noteRepo->findNotesQuery(new NoteSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindAllNotesOfSupportQueryWithoutFilters(): void
    {
        $qb = $this->noteRepo->findNotesOfSupportQuery($this->supportGroup->getId(), new SupportNoteSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindAllNotesOfSupportQueryWithFilters(): void
    {
        $qb = $this->noteRepo->findNotesOfSupportQuery($this->supportGroup->getId(), $this->search);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindAllNotesOfSupportQueryWithFilterByTitle(): void
    {
        $qb = $this->noteRepo->findNotesOfSupportQuery($this->supportGroup->getId(), $this->search->setContent('Note test'));
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindAllNotesOfUser(): void
    {
        $this->assertGreaterThanOrEqual(1, count($this->noteRepo->findNotesOfUser($this->user)));
    }

    public function testCountAllNotesWithoutCriteria(): void
    {
        $this->assertGreaterThanOrEqual(5, $this->noteRepo->countNotes());
    }

    public function testCountAllNotesWithCriteria(): void
    {
        $this->assertGreaterThanOrEqual(5, $this->noteRepo->countNotes(['user' => $this->user]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->noteRepo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->search = null;
    }
}
