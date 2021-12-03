<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Place;
use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Form\Model\Organization\PlaceSearch;
use App\Repository\Organization\PlaceRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlaceRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var PlaceRepository */
    protected $placeRepo;

    /** @var Place */
    protected $place;

    /** @var Service */
    protected $service;

    /** @var Pole */
    protected $pole;

    /** @var PlaceSearch */
    protected $search;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PlaceFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->placeRepo = $this->entityManager->getRepository(Place::class);

        $this->service = $fixtures['service1'];
        $this->search = (new PlaceSearch())
            ->setName('Logement')
            ->setNbPlaces(6)
            ->setStart(new \DateTime('2010-01-01'))
            ->setEnd(new \DateTime('2020-01-01'))
            ->setCity('Houille')
            ->setPole($this->entityManager->getRepository(Pole::class)->findOneBy(['name' => 'AVDL']));
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(5, $this->placeRepo->count([]));
    }

    public function testFindAllPlacesQueryWithoutFilters()
    {
        $qb = $this->placeRepo->findPlacesQuery(new PlaceSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindAllPlacesQueryWithFilters()
    {
        $qb = $this->placeRepo->findPlacesQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindPlacesToExportQueryWithFilters()
    {
        $this->assertGreaterThanOrEqual(1, $this->placeRepo->findPlacesToExport($this->search));
    }

    public function testGetPlacesQueryBuilder()
    {
        $qb = $this->placeRepo->getPlacesQueryBuilder($this->service);
        $this->assertGreaterThanOrEqual(1, count($qb->getQuery()->getResult()));
    }

    public function testfindPlacesOfService()
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
