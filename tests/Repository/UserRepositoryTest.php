<?php

namespace App\Tests\Repository;

use App\Entity\Organization\Pole;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Form\Model\Organization\UserSearch;
use App\Repository\Organization\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRepositoryTest extends WebTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var UserRepository */
    protected $userRepo;

    /** @var User */
    protected $user;

    /** @var Service */
    protected $service;

    /** @var UserSearch */
    protected $search;

    protected function setUp(): void
    {
        /** @var AbstractDatabaseTool */
        $databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();

        $fixtures = $databaseTool->loadAliceFixture([
            dirname(__DIR__).'/fixtures/app_fixtures_test.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var UserRepository */
        $this->userRepo = $this->entityManager->getRepository(User::class);

        $this->user = $fixtures['john_user'];
        $this->service = $fixtures['service1'];
        $this->search = $this->getUserSearch($fixtures['pole1']);
    }

    protected function getUserSearch(Pole $pole): UserSearch
    {
        $poles = new ArrayCollection();
        $poles->add($pole);

        return (new UserSearch())
            ->setFirstname('John')
            ->setLastname('USER')
            ->setPhone('01 00 00 00 00')
            ->setStatus([1])
            ->setPoles($poles);
    }

    public function testCount(): void
    {
        $this->assertGreaterThanOrEqual(5, $this->userRepo->count([]));
    }

    public function testFindUserByUsername(): void
    {
        $this->assertNotNull($this->userRepo->findUser('r.super_admin'));
    }

    public function testFindUserByEmail(): void
    {
        $this->assertNotNull($this->userRepo->findUser('r.super_admin@mail.fr'));
    }

    public function testFindUserById(): void
    {
        $this->assertNotNull($this->userRepo->findUserById($this->user->getId()));
    }

    public function testFindUsersQueryWithoutFilters(): void
    {
        $result = $this->userRepo->findUsersQuery(new UserSearch())->getResult();
        $this->assertGreaterThanOrEqual(5, count($result));
    }

    public function testFindUsersQueryWithFilters(): void
    {
        $result = $this->userRepo->findUsersQuery($this->search)->getResult();
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testFindUsersToExport(): void
    {
        $users = $this->userRepo->findUsersToExport($this->search);
        $this->assertGreaterThanOrEqual(1, count($users));
    }

    /**
     * Méthodes non testables.
     */

    // public function testGetSupportReferentsQueryBuilder()
    // {
    //     $this->assertNotNull($this->userRepo->getSupportReferentsQueryBuilder();
    // }

    // public function testGetAllUsersOfServicesQueryBuilder()
    // {
    //     $this->assertNotNull($this->userRepo->getReferentsOfServicesQueryBuilder();
    // }

    public function testfindUsersOfService(): void
    {
        $users = $this->userRepo->findUsersOfService($this->service);
        $this->assertGreaterThanOrEqual(1, count($users));
    }

    public function testFindUsersWithoutCriteria(): void
    {
        $this->assertGreaterThanOrEqual(5, count($this->userRepo->findUsers()));
    }

    public function testFindUsersWithCriteria(): void
    {
        $this->assertGreaterThanOrEqual(1, count($this->userRepo->findUsers(['status' => 1])));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
        $this->userRepo = null;
        $this->user = null;
        $this->service = null;
        $this->search = null;
    }
}
