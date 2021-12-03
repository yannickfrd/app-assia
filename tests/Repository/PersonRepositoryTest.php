<?php

namespace App\Tests\Repository;

use App\Entity\People\Person;
use App\Form\Model\People\PersonSearch;
use App\Repository\People\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class PersonRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var PersonRepository */
    protected $personRepo;

    /** @var Person */
    protected $person;

    /** @var PersonSearch */
    protected $search;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var PersonRepository */
        $this->personRepo = $this->entityManager->getRepository(Person::class);

        $this->person = $fixtures['userRoleUser'];
        $this->search = $this->getPersonSearch();
    }

    protected function getPersonSearch()
    {
        return (new PersonSearch())
            ->setFirstname('John')
            ->setLastname('DOE')
            ->setBirthdate(new \DateTime('1980-01-01'));
    }

    public function testCount()
    {
        // $people = self::$container->get(PersonRepository::class)->count([]);
        $this->assertGreaterThanOrEqual(5, $this->personRepo->count([]));
    }

    public function testfindPersonById()
    {
        $this->assertNotNull($this->personRepo->findPersonById($this->person->getId()));
    }

    public function testFindAllPeopleQueryWithoutFilters()
    {
        $qb = $this->personRepo->findPeopleQuery(new PersonSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindAllPeopleQueryWithFilters()
    {
        $qb = $this->personRepo->findPeopleQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindAllPeopleQueryWithSearch()
    {
        $qb = $this->personRepo->findPeopleQuery(new PersonSearch(), 'John');
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindPeopleToExport()
    {
        $this->assertGreaterThanOrEqual(5, count($this->personRepo->findPeopleToExport(new PersonSearch())));
    }

    public function testFindPeopleByResearch()
    {
        $this->assertGreaterThanOrEqual(1, count($this->personRepo->findPeopleByResearch('do')));
    }

    public function testCountAllPeople()
    {
        $this->assertGreaterThanOrEqual(5, $this->personRepo->countPeople());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->personRepo = null;
        $this->person = null;
        $this->search = null;
    }
}
