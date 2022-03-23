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
            dirname(__DIR__).'/fixtures/app_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/service_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/person_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/support_fixtures_test.yaml',
            dirname(__DIR__).'/fixtures/rdv_fixtures_test.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var RdvRepository */
        $this->rdvRepo = $this->entityManager->getRepository(Rdv::class);

        $this->supportGroup = $fixtures['support_group1'];
        $this->user = $fixtures['john_user'];

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

    public function testCount(): void
    {
        $this->assertGreaterThanOrEqual(2, $this->rdvRepo->count([]));
    }

    public function testFindAllRdvsQueryWithoutFilters(): void
    {
        $qb = $this->rdvRepo->findRdvsQuery(new RdvSearch());
        $this->assertGreaterThanOrEqual(5, count($qb->getResult()));
    }

    public function testFindAllRdvsQueryWithFilters(): void
    {
        $qb = $this->rdvRepo->findRdvsQuery($this->search);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindAllRdvsQueryOfSupportWithoutFilters(): void
    {
        $qb = $this->rdvRepo->findRdvsQueryOfSupport($this->supportGroup->getId(), new SupportRdvSearch());
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    public function testFindAllRdvsQueryOfSupportWithFilters(): void
    {
        $qb = $this->rdvRepo->findRdvsQueryOfSupport($this->supportGroup->getId(), $this->supportRdvSearch);
        $this->assertGreaterThanOrEqual(1, count($qb->getResult()));
    }

    // public function testindRdvsBetween() {}
    // public function testFindRdvsBetweenByDay() {}

    public function testFindAllRdvsOfUser(): void
    {
        $this->assertGreaterThanOrEqual(1, count($this->rdvRepo->findRdvsOfUser($this->user)));
    }

    public function testCountAllRdvsWithoutCriteria(): void
    {
        $this->assertGreaterThanOrEqual(5, $this->rdvRepo->countRdvs());
    }

    public function testCountAllRdvsWithCriteria(): void
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
