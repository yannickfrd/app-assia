<?php

namespace App\Tests\Repository;

use App\DataFixtures\A_ServiceFixtures;
use App\DataFixtures\B_UserFixtures;
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
        $kernel = self::bootKernel();

        $this->loadFixtures([A_ServiceFixtures::class, B_UserFixtures::class], true);

        $this->repo = self::$container->get(UserRepository::class);
    }

    public function testCount()
    {
        $users = $this->repo->count([]);
        $this->assertGreaterThanOrEqual(20, $users);
    }

    public function testFindUserByUsername()
    {
        $this->assertNotNull($this->repo->findUserByUsernameOrEmail('r.madelaine'));
    }
}
