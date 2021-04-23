<?php

namespace App\Tests\Repository;

use App\Entity\Organization\User;
use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\RdvSearch;
use App\Form\Model\Support\SupportRdvSearch;
use App\Repository\Support\RdvRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RdvRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var RdvRepository */
    protected $repo;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var User */
    protected $user;

    /** @var RdvSearch */
    protected $search;

    /** @var SupportRdvSearch */
    protected $supportRdvSearch;

    protected function setUp()
    {
        $data = $this->loadFixtureFiles([
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
        $this->repo = $this->entityManager->getRepository(Rdv::class);

        $this->supportGroup = $data['supportGroup1'];
        $this->user = $data['userRoleUser'];

        $referents = new ArrayCollection();
        $referents->add($this->user);

        $this->search = (new RdvSearch())
            ->setTitle('Rdv 666')
            ->setStart(new \DateTime('2020-01-01'))
            ->setEnd(new \DateTime())
            ->setReferents($referents);

        $this->supportRdvSearch = (new SupportRdvSearch())
            ->setTitle('Rdv 666')
            ->setStart(new \DateTime('2020-01-01'))
            ->setEnd(new \DateTime());
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(2, $this->repo->count([]));
    }

    public function testFindAllRdvsQueryWithoutFilters()
    {
        $query = $this->repo->findRdvsQuery(new RdvSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllRdvsQueryWithFilters()
    {
        $query = $this->repo->findRdvsQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllRdvsQueryOfSupportWithoutFilters()
    {
        $query = $this->repo->findRdvsQueryOfSupport($this->supportGroup->getId(), new SupportRdvSearch());
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllRdvsQueryOfSupportWithFilters()
    {
        $query = $this->repo->findRdvsQueryOfSupport($this->supportGroup->getId(), $this->supportRdvSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    // public function testindRdvsBetween()
    // {
    // }

    // public function testFindRdvsBetweenByDay()
    // {
    // }

    public function testFindAllRdvsOfUser()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findRdvsOfUser($this->user)));
    }

    public function testCountAllRdvsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countRdvs());
    }

    public function testCountAllRdvsWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countRdvs(['user' => $this->user]));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->search = null;
    }
}
