<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Form\Model\Organization\ServiceSearch;
use App\Repository\Organization\ServiceRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var ServiceRepository */
    protected $serviceRepo;

    /** @var Service */
    protected $service;

    /** @var Pole */
    protected $pole;

    /** @var User */
    protected $user;

    /** @var ServiceSearch */
    protected $search;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->serviceRepo = $this->entityManager->getRepository(Service::class);

        $this->service = $fixtures['service1'];
        $this->pole = $fixtures['pole1'];
        $this->user = $fixtures['userRoleUser'];
        $this->search = (new ServiceSearch())
            ->setName('CHRS Cergy')
            ->setEmail('chrs@mail.fr')
            ->setCity('Pontoise')
            ->setPole($this->pole)
            ->setPhone('01 00 00 00 00');
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(5, $this->serviceRepo->count([]));
    }

    public function testFindAllServicesQueryWithoutFilters()
    {
        $qb = $this->serviceRepo->findServicesQuery(new ServiceSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindAllServicesQueryWithFilters()
    {
        $qb = $this->serviceRepo->findServicesQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindServicesToExportWithFilters()
    {
        $this->assertGreaterThanOrEqual(1, count($this->serviceRepo->findServicesToExport($this->search)));
    }

    // public function testGetServicesOfUserQueryBuilder()
    // {
    // }

    public function testFindAllServicesOfUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->serviceRepo->findServicesOfUser($this->user)));
    }

    public function testGetFullService()
    {
        $this->assertNotNull($this->serviceRepo->getFullService($this->service->getId()));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->serviceRepo = null;
        $this->service = null;
        $this->pole = null;
        $this->user = null;
        $this->search = null;
    }
}
