<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserTest extends KernelTestCase
{
    // protected $passwordEncoder;

    // public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    // {
    //     $this->passwordEncoder = $passwordEncoder;
    // }


    public function getUser(): User
    {
        $faker = \Faker\Factory::create("fr_FR");

        $user = new User;

        $now = new \DateTime();

        return $user
            ->setUsername($faker->firstname())
            ->setFirstName($faker->firstname())
            ->setLastName($faker->lastName())
            ->setStatus(1)
            ->setRoles(["ROLE_USER"])
            ->setPassword("test123")
            ->setEmail($faker->email())
            ->setEnabled(true)
            ->setLoginCount(mt_rand(0, 99))
            ->setLastLogin($now)
            ->setCreatedAt($now)
            ->setUpdatedAt($now);
    }

    public function assertHasErrors(User $user, int $number = 0)
    {
        self::bootKernel();
        $errors = self::$container->get("validator")->validate($user);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . " => " . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(", ", $messages));
    }

    public function testValidPassword()
    {
        $user = $this->getUser()
            ->setPassword("Test123!");

        $this->assertHasErrors($user, 0);
    }

    public function testBlanckUsername()
    {
        $user = $this->getUser()
            ->setUsername("")
            ->setPassword("");

        $this->assertHasErrors($user, 1);
    }

    public function testUsername()
    {
        $user = $this->getUser()
            ->setUsername("")
            ->setPassword("");

        $this->assertHasErrors($user, 1);
    }

    public function testBlanckPassword()
    {
        $user = $this->getUser()
            ->setPassword("");

        $this->assertHasErrors($user, 1);
    }

    public function testInvalidPassword()
    {
        $user = $this->getUser()
            ->setPassword("test123");

        $this->assertHasErrors($user, 1);
    }
}
