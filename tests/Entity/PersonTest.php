<?php

namespace App\Tests\Entity;

use App\Entity\People\Person;
use App\Tests\Entity\AssertHasErrorsTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonTest extends WebTestCase
{
    use FixturesTrait;
    use AssertHasErrorsTrait;

    /** @var Person */
    protected $person;

    protected function setUp(): void
    {
        $this->loadFixtureFiles([
            dirname(__DIR__).'/DataFixturesTest/UserFixturesTest.yaml',
            dirname(__DIR__).'/DataFixturesTest/PersonFixturesTest.yaml',
        ]);

        $faker = \Faker\Factory::create('fr_FR');

        $this->person = (new Person())
            ->setFirstName($faker->firstname())
            ->setLastName($faker->lastName())
            ->setGender(mt_rand(1, 3))
            ->setBirthdate($faker->dateTimeBetween($startDate = '-55 years', $endDate = '-18 years', $timezone = null))
            ->setEmail($faker->email());
    }

    public function testValidPerson()
    {
        $this->assertHasErrors($this->person, 0);
    }

    public function testBlankFirstname()
    {
        $this->assertHasErrors($this->person->setFirstname(''), 2);
    }

    public function testInvalidFirstname()
    {
        $this->assertHasErrors($this->person->setFirstname('x'), 1);
    }

    public function testBlankLastname()
    {
        $this->assertHasErrors($this->person->setLastname(''), 2);
    }

    public function testInvalidLastname()
    {
        $this->assertHasErrors($this->person->setLastname('x'), 1);
    }

    public function testInvalidEmail()
    {
        $this->assertHasErrors($this->person->setEmail('xxxx@xxx'), 1);
    }

    public function testPersonExists()
    {
        $person = $this->person
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setBirthdate(new \DateTime('1980-01-01'));
        $this->assertHasErrors($person, 1);
    }

    protected function tearDown(): void
    {
        $this->person = null;
    }
}
