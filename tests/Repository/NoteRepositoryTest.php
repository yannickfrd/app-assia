<?php

namespace App\Tests\Repository;

use App\Entity\Organization\User;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\NoteSearch;
use App\Form\Model\Support\SupportNoteSearch;
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

    protected function setUp(): void
    {
        $data = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/NoteFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->repo = $this->entityManager->getRepository(Note::class);

        $this->supportGroup = $data['supportGroup1'];
        $this->user = $data['userRoleUser'];
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
        $query = $this->repo->findNotesQuery(new NoteSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllNotesOfSupportQueryWithoutFilters()
    {
        $query = $this->repo->findNotesOfSupportQuery($this->supportGroup->getId(), new SupportNoteSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllNotesOfSupportQueryWithFilters()
    {
        $query = $this->repo->findNotesOfSupportQuery($this->supportGroup->getId(), $this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllNotesOfSupportQueryWithFilterByTitle()
    {
        $query = $this->repo->findNotesOfSupportQuery($this->supportGroup->getId(), $this->search->setContent('Note 666'));
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllNotesOfUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findNotesOfUser($this->user)));
    }

    public function testCountAllNotesWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countNotes());
    }

    public function testCountAllNotesWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countNotes(['user' => $this->user]));
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
