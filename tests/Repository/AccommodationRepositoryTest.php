<?php

namespace App\Tests\Repository;

use App\Entity\Pole;
use App\Entity\Service;
use App\Entity\Accommodation;
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

    /** @var Accommodation  */
    protected $accommodation;

    /** @var Service */
    protected $service;

    /** @var Pole */
    protected $pole;

    /** @var AccommodationSearch */
    protected $accommodationSearch;


    protected function setUp()
    {
        $datafixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixtures/AccommodationFixturesTest.yaml"
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var AccommodationRepository */
        $this->repo = $this->entityManager->getRepository(Accommodation::class);

        $this->service = $datafixtures["service"];
        $this->accommodationSearch = (new AccommodationSearch())
            ->setName("Logement")
            ->setPlacesNumber(6)
            ->setStartDate(new \DateTime("2010-01-01"))
            ->setEndDate(new \DateTime("2020-01-01"))
            ->setCity("Houille")
            ->setPole($this->entityManager->getRepository(Pole::class)->findOneBy(["name" => "AVDL"]));
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
        $query = $this->repo->findAllAccommodationsQuery($this->accommodationSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAccommodationsToExportQueryWithFilters()
    {
        $this->assertGreaterThanOrEqual(1, $this->repo->findAccommodationsToExport($this->accommodationSearch));
    }

    public function testGetAccommodationsQueryList()
    {
        $query = $this->repo->getAccommodationsQueryList($this->service);
        $this->assertGreaterThanOrEqual(1, count($query->getQuery()->getResult()));
    }

    public function testFindAccommodationsFromService()
    {
        $this->assertGreaterThanOrEqual(1, $this->repo->findAccommodationsFromService($this->service));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->service = null;
        $this->accommodationSearch = null;
    }
}
