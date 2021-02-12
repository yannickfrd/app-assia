<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Place;
use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Form\Model\Organization\PlaceSearch;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlaceRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var PlaceRepository */
    protected $repo;

    /** @var Place */
    protected $place;

    /** @var Service */
    protected $service;

    /** @var Pole */
    protected $pole;

    /** @var PlaceSearch */
    protected $search;

    protected function setUp()
    {
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PlaceFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var PlaceRepository */
        $this->repo = $this->entityManager->getRepository(Place::class);

        $this->service = $dataFixtures['service1'];
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
        $this->assertGreaterThanOrEqual(5, $this->repo->count([]));
    }

    public function testFindAllPlacesQueryWithoutFilters()
    {
        $query = $this->repo->findPlacesQuery(new PlaceSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllPlacesQueryWithFilters()
    {
        $query = $this->repo->findPlacesQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindPlacesToExportQueryWithFilters()
    {
        $this->assertGreaterThanOrEqual(1, $this->repo->findPlacesToExport($this->search));
    }

    public function testGetPlacesQueryList()
    {
        $query = $this->repo->getPlacesQueryList($this->service->getId());
        $this->assertGreaterThanOrEqual(1, count($query->getQuery()->getResult()));
    }

    public function testfindPlacesOfService()
    {
        $this->assertGreaterThanOrEqual(1, $this->repo->findPlacesOfService($this->service));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->service = null;
        $this->search = null;
    }
}
