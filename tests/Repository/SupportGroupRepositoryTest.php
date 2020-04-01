<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Form\Model\SupportGroupSearch;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SupportGroupRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /** @var SupportGroupRepository */
    protected $repo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Service */
    protected $service;

    /** @var User */
    protected $user;

    /** @var SupportGroupSearch */
    protected $supportGroupSearch;


    protected function setUp()
    {
        $dataFixtures  = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/SupportFixturesTest.yaml",
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var SupportGroupRepository */
        $this->repo = $this->entityManager->getRepository(SupportGroup::class);

        $this->supportGroup = $dataFixtures["supportGroup1"];
        $this->service =  $dataFixtures["service"];
        $this->user = $dataFixtures["userSuperAdmin"];
        $this->supportGroupSearch = $this->getSupportGroupSearch();
    }

    protected function getSupportGroupSearch()
    {
        return (new SupportGroupSearch())
            ->setFullName("John Doe")
            ->setFamilyTypology(1)
            ->setStartDate(new \DateTime("2018-01-01"))
            ->setEndDate(new \DateTime())
            ->setReferent($this->user);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->count([]));
    }

    public function testFindSupportById()
    {
        $this->assertNotNull($this->repo->findSupportById($this->supportGroup->getId()));
    }

    public function testFindFullSupportById()
    {
        $this->assertNotNull($this->repo->findFullSupportById($this->supportGroup->getId()));
    }

    // public function testFindAllSupportsQueryWithoutFilters()
    // {
    //     $query = $this->repo->findAllSupportsQuery(new SupportGroupSearch());
    //     $this->assertGreaterThanOrEqual(50, count($query->getResult()));
    // }

    // public function testFindAllSupportsQueryWithFilters()
    // {
    //     $query = $this->repo->findAllSupportsQuery($this->supportGroupSearch);
    //     $this->assertGreaterThanOrEqual(50, count($query->getResult()));
    // }

    // public function testGetSupportsWithFilters()
    // {
    //     $query = $this->repo->getSupports(new SupportGroupSearch());
    //     $this->assertGreaterThanOrEqual(50, count($query->getResult()));
    // }

    // public function testGetSupportsWithoutFilters()
    // {
    //     $query = $this->repo->getSupports($this->supportGroupSearch);
    //     $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    // }

    public function testFindAllSupportsFromUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findAllSupportsFromUser($this->user)));
    }

    public function testCountAllSupportsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllSupports());
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->supportGroup = null;
        $this->service = null;
        $this->user = null;
        $this->supportGroupSearch = null;
    }
}
