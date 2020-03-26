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

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var AccommodationRepository
     */
    protected $repo;

    /**
     * @var Accommodation
     */
    protected $accommodation;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @var Pole
     */
    protected $pole;

    /**
     * @var AccommodationSearch
     */
    protected $accommodationSearch;


    protected function setUp()
    {
        $this->loadFixtureFiles([
            dirname(__DIR__, 2) . "/fixtures/UserFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/ServiceFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/PoleFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/AccommodationFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/DeviceFixtures.yaml"
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var AccommodationRepository */
        $this->repo = $this->entityManager->getRepository(Accommodation::class);

        /** @var ServiceRepository */
        $repoService = $this->entityManager->getRepository(Service::class);

        /** @var PoleRepository */
        $repoPole = $this->entityManager->getRepository(Pole::class);

        $this->accommodation = $this->repo->findOneBy(["name" => "r.madelaine"]);

        $this->service = $repoService->findOneBy(["name" => "AVDL"]);

        $this->pole = $repoPole->findOneBy(["name" => "AVDL"]);

        $this->accommodationSearch = $this->getAccommodationSearch();
    }

    protected function getAccommodationSearch()
    {
        return (new AccommodationSearch())
            ->setName("Logement")
            ->setPlacesNumber(6)
            ->setStartDate(new \DateTime("2010-01-01"))
            ->setEndDate(new \DateTime("2020-01-01"))
            ->setCity("Houille")
            ->setPole($this->pole);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(100, $this->repo->count([]));
    }

    public function testFindAllAccommodationsQueryWithoutFilters()
    {
        $query = $this->repo->findAllAccommodationsQuery(new AccommodationSearch());
        $this->assertGreaterThanOrEqual(100, count($query->getResult()));
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

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
