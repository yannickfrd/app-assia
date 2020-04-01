<?php

namespace App\Tests\Repository;

use App\Entity\User;
use App\Entity\Rdv;
use App\Entity\SupportGroup;
use App\Form\Model\RdvSearch;
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
    protected $rdvSearch;


    protected function setUp()
    {
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__) . "/DataFixturesTest/RdvFixturesTest.yaml",
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get("doctrine")
            ->getManager();

        /** @var RdvRepository */
        $this->repo = $this->entityManager->getRepository(Rdv::class);

        $this->supportGroup = $dataFixtures["supportGroup"];
        $this->user = $dataFixtures["userSuperAdmin"];
        $this->rdvSearch = (new RdvSearch())
            ->setTitle("Rdv 666")
            ->setStartDate(new \DateTime("2020-01-01"))
            ->setEndDate(new \DateTime())
            ->setReferent("Romain");
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
        $query = $this->repo->findAllRdvsQuery($this->rdvSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }


    public function testFindAllRdvsQueryFromSupportWithoutFilters()
    {
        $query = $this->repo->findAllRdvsQueryFromSupport($this->supportGroup->getId(), new RdvSearch());
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindAllRdvsQueryFromSupportWithFilters()
    {
        $query = $this->repo->findAllRdvsQueryFromSupport($this->supportGroup->getId(), $this->rdvSearch);
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
        $this->assertGreaterThanOrEqual(1, count($this->repo->findAllRdvsFromUser($this->user)));
    }

    public function testCountAllRdvsWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllRdvs());
    }

    public function testCountAllRdvsWithCriteria()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->countAllRdvs(["user" => $this->user]));
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->supportGroup = null;
        $this->user = null;
        $this->rdvSearch = null;
    }
}
