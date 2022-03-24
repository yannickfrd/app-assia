<?php

namespace App\Tests\Entity;

use App\Entity\Organization\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    use AssertHasErrorsTrait;

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var User */
    protected $user;

    protected function setUp(): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $firstname = $faker->firstname();
        $lastname = $faker->lastName();
        $username = $firstname.'.'.$lastname;
        $now = new \DateTime();

        $this->user = (new User())
            ->setUsername($username)
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setStatus(1)
            ->setRoles(['ROLE_USER'])
            ->setPassword('Password123!')
            ->setEmail($faker->email())
            ->setLoginCount(mt_rand(0, 99))
            ->setLastLogin($now);
    }

    public function testValidUser(): void
    {
        $this->assertHasErrors($this->user, 0);
    }

    public function testBlankFirstname(): void
    {
        $this->assertHasErrors($this->user->setFirstname(''), 2);
    }

    public function testInvalidFirstname(): void
    {
        $this->assertHasErrors($this->user->setFirstname('x'), 1);
    }

    public function testBlankLastname(): void
    {
        $this->assertHasErrors($this->user->setLastname(''), 2);
    }

    public function testInvalidLastname(): void
    {
        $this->assertHasErrors($this->user->setLastname('x'), 1);
    }

    public function testBlankUsername(): void
    {
        $this->assertHasErrors($this->user->setUsername(''), 1);
    }

    public function testBlankEmail(): void
    {
        $this->assertHasErrors($this->user->setEmail(''), 1);
    }

    public function testInvalidEmail(): void
    {
        $this->assertHasErrors($this->user->setEmail('xxxx@xxx'), 1);
    }

    protected function tearDown(): void
    {
        $this->user = null;
    }
}
