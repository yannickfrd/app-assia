<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Form\Model\Organization\UserSearch;
use App\Repository\Organization\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRepositoryTest extends WebTestCase
{
    use FixturesTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var UserRepository */
    protected $repo;

    /** @var User */
    protected $user;

    /** @var Service */
    protected $service;

    /** @var UserSearch */
    protected $search;

    protected function setUp()
    {
        $data = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var UserRepository */
        $this->repo = $this->entityManager->getRepository(User::class);

        $this->user = $data['userRoleUser'];
        $this->service = $data['service1'];
        $this->search = $this->getUserSearch($data['pole1']);
    }

    protected function getUserSearch(Pole $pole)
    {
        $poles = new ArrayCollection();
        $poles->add($pole);

        return (new UserSearch())
            ->setFirstname('Role')
            ->setLastname('USER')
            ->setPhone('01 00 00 00 00')
            ->setStatus([1])
            ->setPoles($poles);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->count([]));
    }

    public function testFindUserByUsername()
    {
        $this->assertNotNull($this->repo->findUser('r.super_admin'));
    }

    public function testFindUserByEmail()
    {
        $this->assertNotNull($this->repo->findUser('r.super_admin@mail.fr'));
    }

    public function testFindUserById()
    {
        $this->assertNotNull($this->repo->findUserById($this->user->getId()));
    }

    public function testFindUsersQueryWithoutFilters()
    {
        $result = $this->repo->findUsersQuery(new UserSearch())->getResult();
        $this->assertGreaterThanOrEqual(5, count($result));
    }

    public function testFindUsersQueryWithFilters()
    {
        $result = $this->repo->findUsersQuery($this->search)->getResult();
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindUsersToExport()
    {
        $users = $this->repo->findUsersToExport($this->search);
        $this->assertGreaterThanOrEqual(1, count($users));
    }

    /**
     * Méthodes non testables.
     */

    // public function testGetUsersQueryBuilder()
    // {
    //     $this->assertNotNull($this->repo->getUsersQueryBuilder();
    // }

    // public function testGetAllUsersOfServicesQueryBuilder()
    // {
    //     $this->assertNotNull($this->repo->getReferentsOfServicesQueryBuilder();
    // }

    public function testfindUsersOfService()
    {
        $users = $this->repo->findUsersOfService($this->service);
        $this->assertGreaterThanOrEqual(1, count($users));
    }

    public function testFindUsersWithoutCriteria()
    {
        $this->assertGreaterThanOrEqual(5, count($this->repo->findUsers()));
    }

    public function testFindUsersWithCriteria()
    {
        $this->assertGreaterThanOrEqual(1, count($this->repo->findUsers(['status' => 1])));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repo = null;
        $this->user = null;
        $this->service = null;
        $this->search = null;
    }
}
