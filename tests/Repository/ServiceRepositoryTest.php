<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Form\Model\Organization\ServiceSearch;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var ServiceRepository */
    protected $repo;

    /** @var Service */
    protected $service;

    /** @var Pole */
    protected $pole;

    /** @var User */
    protected $user;

    /** @var ServiceSearch */
    protected $search;

    protected function setUp()
    {
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var ServiceRepository */
        $this->repo = $this->entityManager->getRepository(Service::class);

        $this->service = $dataFixtures['service1'];
        $this->pole = $dataFixtures['pole'];
        $this->user = $dataFixtures['userSuperAdmin'];
        $this->search = (new ServiceSearch())
            ->setName('AVDL')
            ->setEmail('avdl@esperer-95.org')
            ->setCity('Pontoise')
            ->setPole($this->pole)
            ->setPhone('01 00 00 00 00');
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->count([]));
    }

    public function testFindAllServicesQueryWithoutFilters()
    {
        $query = $this->repo->findAllServicesQuery(new ServiceSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllServicesQueryWithFilters()
    {
        $query = $this->repo->findAllServicesQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindServicesToExportWithFilters()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findServicesToExport($this->search)));
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

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->service = null;
        $this->pole = null;
        $this->user = null;
        $this->search = null;
    }
}
