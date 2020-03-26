<?php

namespace App\Tests\Repository;

use App\Entity\Pole;
use App\Entity\User;
use App\Entity\Service;
use App\Form\Model\ServiceSearch;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceRepositoryTest extends WebTestCase
{

    use FixturesTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var ServiceRepository
     */
    protected $repo;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @var Pole
     */
    protected $pole;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var ServiceSearch
     */
    protected $serviceSearch;


    protected function setUp()
    {
        $this->loadFixtureFiles([
            dirname(__DIR__, 2) . "/fixtures/UserFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/ServiceFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/PoleFixtures.yaml"
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var ServiceRepository */
        $this->repo = $this->entityManager->getRepository(Service::class);

        /** @var PoleRepository */
        $repoPole = $this->entityManager->getRepository(Pole::class);

        /** @var PoleRepository */
        $repoUser = $this->entityManager->getRepository(User::class);

        $this->service = $this->repo->findOneBy(["name" => "AVDL"]);
        $this->pole = $repoPole->findOneBy(["name" => "Habitat"]);
        $this->user = $repoUser->findOneBy(["username" => "r.madelaine"]);

        $this->serviceSearch = $this->getServiceSearch();
    }

    protected function getServiceSearch()
    {
        return (new ServiceSearch())
            ->setName("AVDL")
            ->setEmail("avdl@esperer-95.org")
            ->setCity("Pontoise")
            ->setPole($this->pole)
            ->setPhone("01 00 00 00 00");
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(10, $this->repo->count([]));
    }

    public function testFindAllServicesQueryWithoutFilters()
    {
        $query = $this->repo->findAllServicesQuery(new ServiceSearch());
        $this->assertGreaterThanOrEqual(10, count($query->getResult()));
    }

    public function testFindAllServicesQueryWithFilters()
    {
        $query = $this->repo->findAllServicesQuery($this->serviceSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindServicesToExportWithFilters()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findServicesToExport($this->serviceSearch)));
    }

    // public function testGetServicesFromUserQueryList()
    // {
    // }

    public function testFindAllServicesFromUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findAllServicesFromUser($this->user)));
    }

    public function testGetFullService()
    {
        $this->assertNotNull($this->repo->getFullService($this->service->getId()));
    }
}
