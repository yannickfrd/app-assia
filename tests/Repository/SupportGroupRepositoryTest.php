<?php

namespace App\Tests\Repository;

use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Form\Model\SupportGroupSearch;
use Doctrine\Common\Collections\ArrayCollection;
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
    protected $search;

    protected function setUp()
    {
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/SupportFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var SupportGroupRepository */
        $this->repo = $this->entityManager->getRepository(SupportGroup::class);

        $this->supportGroup = $dataFixtures['supportGroup1'];
        $this->service = $dataFixtures['service'];
        $this->user = $dataFixtures['userRoleUser'];
        $this->search = $this->getSupportGroupSearch();
    }

    protected function getSupportGroupSearch()
    {
        $referents = new ArrayCollection();
        $referents->add($this->user);

        return (new SupportGroupSearch())
            ->setFullName('John Doe')
            ->setFamilyTypologies([1])
            ->setStart(new \DateTime('2018-01-01'))
            ->setEnd(new \DateTime())
            ->setReferents($referents);
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
    //     $query = $this->repo->findAllSupportsQuery($this->search);
    //     $this->assertGreaterThanOrEqual(50, count($query->getResult()));
    // }

    // public function testGetSupportsWithFilters()
    // {
    //     $query = $this->repo->getSupports(new SupportGroupSearch());
    //     $this->assertGreaterThanOrEqual(50, count($query->getResult()));
    // }

    // public function testGetSupportsWithoutFilters()
    // {
    //     $query = $this->repo->getSupports($this->search);
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

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->supportGroup = null;
        $this->service = null;
        $this->user = null;
        $this->search = null;
    }
}
