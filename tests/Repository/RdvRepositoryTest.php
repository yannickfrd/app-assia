<?php

namespace App\Tests\Repository;

use App\Entity\Rdv;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Form\Model\RdvSearch;
use App\Form\Model\SupportRdvSearch;
use App\Repository\RdvRepository;
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
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/RdvFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var RdvRepository */
        $this->repo = $this->entityManager->getRepository(Rdv::class);

        $this->supportGroup = $dataFixtures['supportGroup'];
        $this->user = $dataFixtures['userRoleUser'];

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
        $query = $this->repo->findAllRdvsQuery(new RdvSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllRdvsQueryWithFilters()
    {
        $query = $this->repo->findAllRdvsQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllRdvsQueryFromSupportWithoutFilters()
    {
        $query = $this->repo->findAllRdvsQueryFromSupport($this->supportGroup->getId(), new SupportRdvSearch());
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllRdvsQueryFromSupportWithFilters()
    {
        $query = $this->repo->findAllRdvsQueryFromSupport($this->supportGroup->getId(), $this->supportRdvSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    // public function testindRdvsBetween()
    // {
    // }

    // public function testFindRdvsBetweenByDay()
    // {
    // }

    public function testFindAllRdvsFromUser()
    {
        // dd($this->repo->findAllRdvsFromUser($this->user));

        $this->assertGreaterThanOrEqual(1, count($this->repo->findAllRdvsFromUser($this->user)));
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
