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
            dirname(__DIR__).'/fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/person_fixtures_test.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var PersonRepository */
        $this->personRepo = $this->entityManager->getRepository(Person::class);

        $this->person = $fixtures['john_user'];
        $this->search = $this->getPersonSearch();
    }

    protected function getPersonSearch(): PersonSearch
    {
        return (new PersonSearch())
            ->setFirstname('John')
            ->setLastname('DOE')
            ->setBirthdate(new \DateTime('1980-01-01'));
    }

    public function testCount(): void
    {
        // $people = self::$container->get(PersonRepository::class)->count([]);
        $this->assertGreaterThanOrEqual(5, $this->personRepo->count([]));
    }

    public function testfindPersonById(): void
    {
        $this->assertNotNull($this->personRepo->findPersonById($this->person->getId()));
    }

    public function testFindAllPeopleQueryWithoutFilters(): void
    {
        $qb = $this->personRepo->findPeopleQuery(new PersonSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindAllPeopleQueryWithFilters(): void
    {
        $qb = $this->personRepo->findPeopleQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindAllPeopleQueryWithSearch(): void
    {
        $qb = $this->personRepo->findPeopleQuery(new PersonSearch(), 'John');
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindPeopleToExport(): void
    {
        $this->assertGreaterThanOrEqual(5, count($this->personRepo->findPeopleToExport(new PersonSearch())));
    }

    public function testFindPeopleByResearch(): void
    {
        $this->assertGreaterThanOrEqual(1, count($this->personRepo->findPeopleByResearch('do')));
    }

    public function testCountAllPeople(): void
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
