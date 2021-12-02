<?php

namespace App\Tests\Repository;

use App\Entity\Organization\User;
use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\RdvSearch;
use App\Form\Model\Support\SupportRdvSearch;
use App\Repository\Support\RdvRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RdvRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var RdvRepository */
    protected $rdvRepo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var User */
    protected $user;

    /** @var RdvSearch */
    protected $search;

    /** @var SupportRdvSearch */
    protected $supportRdvSearch;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/ServiceFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/SupportFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/RdvFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var RdvRepository */
        $this->rdvRepo = $this->entityManager->getRepository(Rdv::class);

        $this->supportGroup = $fixtures['supportGroup1'];
        $this->user = $fixtures['userRoleUser'];

        $referents = new ArrayCollection();
        $referents->add($this->user);

        $this->search = (new RdvSearch())
            ->setTitle('RDV test')
            ->setStart(new \DateTime('2020-01-01'))
            ->setEnd(new \DateTime())
            ->setReferents($referents);

        $this->supportRdvSearch = (new SupportRdvSearch())
            ->setTitle('RDV test')
            ->setStart(new \DateTime('2020-01-01'))
            ->setEnd(new \DateTime());
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->rdvRepo->count([]));
    }

    public function testFindAllRdvsQueryWithoutFilters()
    {
        $query = $this->rdvRepo->findRdvsQuery(new RdvSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllRdvsQueryWithFilters()
    {
        $query = $this->rdvRepo->findRdvsQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllRdvsQueryOfSupportWithoutFilters()
    {
        $query = $this->rdvRepo->findRdvsQueryOfSupport($this->supportGroup->getId(), new SupportRdvSearch());
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllRdvsQueryOfSupportWithFilters()
    {
        $query = $this->rdvRepo->findRdvsQueryOfSupport($this->supportGroup->getId(), $this->supportRdvSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    // public function testindRdvsBetween() {}
    // public function testFindRdvsBetweenByDay() {}

    public function testFindAllRdvsOfUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->rdvRepo->findRdvsOfUser($this->user)));
    }

    public function testCountAllRdvsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->rdvRepo->countRdvs());
    }

    public function testCountAllRdvsWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->rdvRepo->countRdvs(['user' => $this->user]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->rdvRepo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->search = null;
    }
}
