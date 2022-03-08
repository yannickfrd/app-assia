<?php

namespace App\Tests\Repository;

use Doctrine\ORM\EntityManager;
use App\Entity\Organization\User;
use App\Entity\Organization\Service;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportSearch;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class SupportGroupRepositoryTest extends WebTestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var SupportGroupRepository */
    protected $supportGroupRepo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var Service */
    protected $service;

    /** @var User */
    protected $user;

    /** @var SupportSearch */
    protected $search;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/SupportFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var SupportGroupRepository */
        $this->supportGroupRepo = $this->entityManager->getRepository(SupportGroup::class);

        $this->supportGroup = $fixtures['supportGroup1'];
        $this->service = $fixtures['service1'];
        $this->user = $fixtures['userRoleUser'];
        $this->search = $this->getSupportSearch();
    }

    protected function getSupportSearch()
    {
        $referents = new ArrayCollection();
        $referents->add($this->user);

        return (new SupportSearch())
            ->setFullName('John Doe')
            ->setFamilyTypologies([1])
            ->setStart(new \DateTime('2018-01-01'))
            ->setEnd(new \DateTime())
            ->setReferents($referents);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(3, $this->supportGroupRepo->count([]));
    }

    public function testFindSupportById()
    {
        $this->assertNotNull($this->supportGroupRepo->findSupportById($this->supportGroup->getId()));
    }

    public function testFindFullSupportById()
    {
        $this->assertNotNull($this->supportGroupRepo->findFullSupportById($this->supportGroup->getId()));
    }

    public function testFindAllSupportsOfUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->supportGroupRepo->findSupportsOfUser($this->user)));
    }

    public function testCountAllSupportsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(3, $this->supportGroupRepo->countSupports());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->supportGroupRepo = null;
        $this->supportGroup = null;
        $this->service = null;
        $this->user = null;
        $this->search = null;
    }
}
