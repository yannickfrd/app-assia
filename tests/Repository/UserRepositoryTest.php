<?php

namespace App\Tests\Repository;

use App\Entity\Pole;
use App\Entity\Service;
use App\Entity\User;
use App\Form\Model\UserSearch;
use App\Repository\UserRepository;
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
    protected $userSearch;

    protected function setUp()
    {
        // $this->loadFixtures([A_ServiceFixtures::class, B_UserFixtures::class]);
        $dataFixtures = $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
        ]);

        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        /* @var UserRepository */
        $this->repo = $this->entityManager->getRepository(User::class);

        $this->user = $dataFixtures['userSuperAdmin'];
        $this->service = $dataFixtures['service'];
        $this->userSearch = $this->getUserSearch($dataFixtures['pole']);
    }

    protected function getUserSearch(Pole $pole)
    {
        return (new UserSearch())
            ->setFirstname('Rom')
            ->setLastname('mad')
            ->setPhone('01 00 00 00 00')
            ->setStatus(6)
            ->setPole($pole);
    }

    public function testCount()
    {
        $this->assertGreaterThanOrEqual(5, $this->repo->count([]));
    }

    public function testFindUserByUsername()
    {
        $this->assertNotNull($this->repo->findUserByUsernameOrEmail('r.madelaine'));
    }

    public function testFindUserByEmail()
    {
        $this->assertNotNull($this->repo->findUserByUsernameOrEmail('romain.madelaine@esperer-95.org'));
    }

    public function testFindUserById()
    {
        $this->assertNotNull($this->repo->findUserById($this->user->getId()));
    }

    public function testFindAllUsersQueryWithoutFilters()
    {
        $query = $this->repo->findAllUsersQuery(new UserSearch());
        $this->assertGreaterThanOrEqual(5, count($query->getResult()));
    }

    public function testFindAllUsersQueryWithFilters()
    {
        $query = $this->repo->findAllUsersQuery($this->userSearch);
        $this->assertGreaterThanOrEqual(1, count($query->getResult()));
    }

    public function testFindUsersToExport()
    {
        $users = $this->repo->findUsersToExport($this->userSearch);
        $this->assertGreaterThanOrEqual(1, count($users));
    }

    /**
     * Méthodes non testatables.
     */

    // public function testGetUsersQueryList()
    // {
    //     $this->assertNotNull($this->repo->getUsersQueryList();
    // }

    // public function testGetAllUsersFromServicesQueryList()
    // {
    //     $this->assertNotNull($this->repo->getAllUsersFromServicesQueryList();
    // }

    public function testFindUsersFromService()
    {
        $users = $this->repo->findUsersFromService($this->service);
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
        $this->userSearch = null;
    }
}
