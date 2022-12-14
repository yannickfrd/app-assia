<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Pole;
use App\Entity\Organization\User;
use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Form\Model\Organization\PlaceSearch;
use App\Repository\Organization\PlaceRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class PlaceRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var PlaceRepository */
    protected $placeRepo;

    /** @var User */
    protected $user;

    /** @var Service */
    protected $service;

    /** @var PlaceSearch */
    protected $search;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/place_fixtures_test.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->placeRepo = $this->entityManager->getRepository(Place::class);

        $this->user = $fixtures['john_user'];

        $this->service = $fixtures['service1'];
        $this->search = (new PlaceSearch())
            ->setName('Logement')
            ->setNbPlaces(6)
            ->setStart(new \DateTime('2010-01-01'))
            ->setEnd(new \DateTime('2020-01-01'))
            ->setCity('Houille')
            ->setPole($this->entityManager->getRepository(Pole::class)->findOneBy(['name' => 'AVDL']));
    }

    public function testCount(): void
    {
        $this->assertGreaterThanOrEqual(5, $this->placeRepo->count([]));
    }

    public function testFindAllPlacesQueryWithoutFilters(): void
    {
        $qb = $this->placeRepo->findPlacesQuery(new PlaceSearch(), $this->user);
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindAllPlacesQueryWithFilters(): void
    {
        $qb = $this->placeRepo->findPlacesQuery($this->search, $this->user);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindPlacesToExportQueryWithFilters(): void
    {
        $this->assertGreaterThanOrEqual(1, $this->placeRepo->findPlacesToExport($this->search));
    }

    public function testGetPlacesQueryBuilder(): void
    {
        $qb = $this->placeRepo->getPlacesQueryBuilder($this->service);
        $this->assertGreaterThanOrEqual(1, count($qb->getQuery()->getResult()));
    }

    public function testfindPlacesOfService(): void
    {
        $this->assertGreaterThanOrEqual(1, $this->placeRepo->findPlacesOfService($this->service));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->placeRepo = null;
        $this->service = null;
        $this->search = null;
    }
}
