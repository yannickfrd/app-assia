<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportSearch;
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

    /** @var SupportSearch */
    protected $search;

    protected function setUp()
    {
        $data = $this->loadFixtureFiles([
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
        $this->repo = $this->entityManager->getRepository(SupportGroup::class);

        $this->supportGroup = $data['supportGroup1'];
        $this->service = $data['service1'];
        $this->user = $data['userRoleUser'];
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
        $this->assertGreaterThanOrEqual(3, $this->repo->count([]));
    }

    public function testFindSupportById()
    {
        $this->assertNotNull($this->repo->findSupportById($this->supportGroup->getId()));
    }

    public function testFindFullSupportById()
    {
        $this->assertNotNull($this->repo->findFullSupportById($this->supportGroup->getId()));
    }

    public function testFindAllSupportsOfUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findSupportsOfUser($this->user)));
    }

    public function testCountAllSupportsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(3, $this->repo->countSupports());
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
