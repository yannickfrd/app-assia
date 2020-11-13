<?php

namespace App\Tests\Repository;

use App\Entity\Accommodation;
use App\Entity\Pole;
use App\Entity\Service;
use App\Form\Model\AccommodationSearch;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccommodationRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var AccommodationRepository */
    protected $repo;

    /** @var Accommodation */
    protected $accommodation;

    /** @var Service */
    protected $service;

    /** @var Pole */
    protected $pole;

    /** @var AccommodationSearch */
    protected $search;

    protected function setUp()
    {
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/AccommodationFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var AccommodationRepository */
        $this->repo = $this->entityManager->getRepository(Accommodation::class);

        $this->service = $dataFixtures['service1'];
        $this->search = (new AccommodationSearch())
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

    public function testFindAllAccommodationsQueryWithoutFilters()
    {
        $query = $this->repo->findAllAccommodationsQuery(new AccommodationSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllAccommodationsQueryWithFilters()
    {
        $query = $this->repo->findAllAccommodationsQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAccommodationsToExportQueryWithFilters()
    {
        $this->assertGreaterThanOrEqual(1, $this->repo->findAccommodationsToExport($this->search));
    }

    public function testGetAccommodationsQueryList()
    {
        $query = $this->repo->getAccommodationsQueryList($this->service->getId());
        $this->assertGreaterThanOrEqual(1, count($query->getQuery()->getResult()));
    }

    public function testfindAccommodationsOfService()
    {
        $this->assertGreaterThanOrEqual(1, $this->repo->findAccommodationsOfService($this->service));
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
