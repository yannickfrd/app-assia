<?php

namespace App\Tests\Repository\Event;

use App\Entity\Event\Task;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Event\TaskSearch;
use App\Repository\Event\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var TaskRepository */
    protected $taskRepo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var User */
    protected $user;

    /** @var TaskSearch */
    protected $taskSearch;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AbstractDatabaseTool */
        $databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

        /** @var array */
        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/../DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/../DataFixturesTest/task_fixtures_test.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->taskRepo = $this->entityManager->getRepository(Task::class);

        $this->supportGroup = $fixtures['supportGroup1'];
        $this->user = $fixtures['userRoleUser'];

        $referents = new ArrayCollection();
        $referents->add($this->user);

        $this->taskSearch = (new TaskSearch())
            ->setTitle('Task test')
            ->setStart(new \DateTime('2020-01-01'))
            ->setEnd(new \DateTime())
            ->setReferents($referents);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->taskRepo->count([]));
    }

    public function testFindAllTasksQueryWithoutFilters()
    {
        $query = $this->taskRepo->findTasksQuery(new TaskSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllTasksQueryWithFilters()
    {
        $query = $this->taskRepo->findTasksQuery($this->taskSearch);
        $this->assertGreaterThanOrEqual(0, count($query->getResult()));
    }

    public function testFindAllTasksQueryOfSupportWithoutFilters()
    {
        $query = $this->taskRepo->findTasksQueryOfSupport($this->supportGroup->getId(), new TaskSearch());
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllTasksQueryOfSupportWithFilters()
    {
        $query = $this->taskRepo->findTasksQueryOfSupport($this->supportGroup->getId(), $this->taskSearch);
        $this->assertGreaterThanOrEqual(0, count($query->getResult()));
    }

    public function testCountAllTasksWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->taskRepo->countTasks());
    }

    public function testCountAllTasksWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->taskRepo->countTasks(['user' => $this->user]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->taskRepo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->taskSearch = null;
    }
}
