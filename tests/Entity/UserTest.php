<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends WebTestCase
{
    use FixturesTrait;
    use AsserthasErrorsTrait;

    /**
     * @var User
     */
    protected $user;

    protected function setUp()
    {
        $this->loadFixtureFiles([
            dirname(__DIR__, 2) . "/fixtures/UserFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/ServiceFixtures.yaml",
            dirname(__DIR__, 2) . "/fixtures/PoleFixtures.yaml"
        ]);

        $this->user = $this->getUser();
    }

    public function getUser(): User
    {
        $faker = \Faker\Factory::create("fr_FR");

        $firstname = $faker->firstname();
        $lastname = $faker->lastName();
        $username = $firstname . "." . $lastname;
        $now = new \DateTime();

        return (new User)
            ->setUsername($username)
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setStatus(1)
            ->setRoles(["ROLE_USER"])
            ->setPassword("Test123*")
            ->setEmail($faker->email())
            ->setEnabled(true)
            ->setLoginCount(mt_rand(0, 99))
            ->setLastLogin($now)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
    }

    public function testValidUser()
    {
        $this->assertHasErrors($this->user, 0);
    }

    public function testBlankFirstname()
    {
        $this->assertHasErrors($this->user->setFirstname(""), 2);
    }

    public function testInvalidFirstname()
    {
        $this->assertHasErrors($this->user->setFirstname("x"), 1);
    }

    public function testBlankLastname()
    {
        $this->assertHasErrors($this->user->setLastname(""), 2);
    }

    public function testInvalidLastname()
    {
        $this->assertHasErrors($this->user->setLastname("x"), 1);
    }

    public function testBlankUsername()
    {
        $this->assertHasErrors($this->user->setUsername(""), 1);
    }

    public function testBlankPassword()
    {
        $this->assertHasErrors($this->user->setPassword(""), 1);
    }

    public function testInvalidPassword()
    {
        $this->assertHasErrors($this->user->setPassword("test123"), 1);
    }

    public function testBlankEmail()
    {
        $this->assertHasErrors($this->user->setEmail(""), 1);
    }

    public function testInvalidEmail()
    {
        $this->assertHasErrors($this->user->setEmail("xxxx@xxx"), 1);
    }

    public function testUsernameExists()
    {
        $user = $this->user->setUsername("r.madelaine");
        $this->assertHasErrors($user, 1);
    }
}
